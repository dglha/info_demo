<?php

namespace App\Http\Controllers;

use App\Code;
use App\Constants\CodeStatusConstants;
use App\Constants\MissionStatusConstants;
use App\Info;
use App\Mission;
use App\Page;
use Carbon\Carbon;
use Doctrine\Inflector\Rules\Word;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Ramsey\Uuid\Uuid;

class MissionController extends Controller
{
  public function test()
  {
    $user  = Redis::set('name', 'hapro');
  }

  public function getData()
  {
    $user = Redis::get('name');
    dd($user);
  }

  public function getUserIpAddr()
  {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
      //ip from share internet
      $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
      //ip pass from proxy
      $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
      $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
  }

  public function postMission(Request $request)
  {
    $userIP = $this->getUserIpAddr();
    $mission = Mission::where('ip', $userIP)
      ->where('user_agent', $request->userAgent())
      ->where('status', MissionStatusConstants::DOING)
      ->orderBy('created_at', 'desc')->first();
    if ($mission) { // There is mission existed!
      $page = Page::where('id', $mission->page_id)
      ->where('status', 1)->first();
      return response()->json(["mission" => $page]);
    }

    $pickedPage = null;
    $now = Carbon::now();
    $excludePageId = [];
    while (!$pickedPage) {
      $page = Page::where('status', 1)
        ->whereNotIn('id', $excludePageId)
        ->inRandomOrder()->first();
      if (!$page) {
        break;
      }
      $mission = Mission::where('page_id', $page->id)
        ->where('status', MissionStatusConstants::COMPLETED)
        ->where('ip', $request->ip())
        ->whereDate('updated_at',  Carbon::today())
        ->orderBy('updated_at', 'desc')->first();

      if (!$mission) {
        $pickedPage = $page;
        break;
      }

      // Find difference from last done mission of this page from user
      $lastMissionTime = new Carbon($mission->updated_at);
      $time = Carbon::parse($now->diff($lastMissionTime)->format('%H:%I:%S'));
      if ($time->gte(Carbon::createFromTimestamp($page->timeout))) {
        $pickedPage = $page;
        break;
      }

      array_push($excludePageId, $page->id);
    }

    if (!$pickedPage) {
      // No page available -> comback later
      return response()->json(["error" => "No mission available"]);
    }
    // Begin database transaction
    $ms = DB::transaction(function () use ($pickedPage, $request, $userIP) {
      // Refresh data
      $pickedPage = $pickedPage->refresh();

      $newMission = new Mission();
      $newMission->page_id = $pickedPage->id;
      $newMission->status = MissionStatusConstants::DOING;
      // $newMission->ip = $request->ip();
      $newMission->ip = $userIP;
      $newMission->user_agent = $request->userAgent();
      $newMission->save();

      $pickedPage->traffic_remain -= 1;
      $pickedPage->save();

      return $newMission;
    });
    return response()->json(["mission" => $pickedPage]);
  }

  public function cancelMission(Request $request)
  {
    // Cancel current mission
    $mission = Mission::where('ip', $request->id)
      ->where('user_agent', $request->userAgent())
      ->where('status', MissionStatusConstants::DOING)
      ->orderBy('created_at', 'desc')->first();

    if ($mission) {

      DB::transaction(function () use ($mission) {

        $mission->status = MissionStatusConstants::CANCEL;
        $mission->save();

        $page = Page::where('id', $mission->page_id)->first();
        if ($page->traffic_remain < $page->traffic_sum) {
          $page->traffic_remain += 1;
          $page->save();
        }
      });
    }
    return response()->json(["status" => "ok"]);
  }

  public function generateCode(Request $rq)
  {
    try {
      $pageId = $rq->pageId;
      $host = $rq->host;
      $path = $rq->path;
      // $uIP = $rq->ip();
      $uIP = $this->getUserIpAddr();
      $uAgent = $rq->userAgent();
      $mission = Mission::where([
        ["ip", $uIP],
        ["user_agent", $uAgent],
        ["page_id", $pageId],
        ["missions.status", MissionStatusConstants::DOING]
      ]);
      //rule here
      $page = Page::where([
        ["id", $pageId],
        ["status", 1],
      ])->get(["onsite", "url"])->first();
      if (empty($page)) {
        return response()->json(["error" => "Traffic của site chưa sẵn sàng"]);
      }
      if (!str_contains($page->url, $host)) {
        return response()->json(["error" => "Lỗi, nhúng không đúng site"]);
      }

      //check this ip don't have mission
      if ($mission->count() === 0) {
        return response()->json(["error" => "Lỗi"]);
      }

      //first count down
      $time = $mission->get('updated_at')->first();
      if (empty($time->updated_at)) {
        $mission->update(['updated_at' => Carbon::now()]);
        return response()->json(["onsite" => $page->onsite]);
      }

      // f5 - or click anything link
      // $code = $mission->get('code')->first();
      $code = Code::where('status', CodeStatusConstants::NEW)->inRandomOrder()->first();
      //check code is exist
      // if (empty($code->code)) {
      if (empty($code)) {
        //generateCode
        $code = new Code();
        $code->code = UUid::uuid4()->toString();
        $code->status = CodeStatusConstants::NEW;
        $code->save();
      }
      //check rule
      $timeDiff = Carbon::now()->diffInSeconds($time->updated_at);
      if ($page->onsite <= $timeDiff) {
        if ($path !== "/") {
          // $uuid = Uuid::uuid4()->toString();
          $code->status = CodeStatusConstants::SENT;
          $code->save();
          // $mission->update(["missions.code" => $uuid->code]);
          return response()->json(["code" => $code->code]);
        } else {
          $mission->update(['updated_at' => Carbon::now()]);
          return response()->json(["onsite" => $page->onsite]);
        }
      } else {
        $mission->update(['updated_at' => Carbon::now()]);
        return response()->json(["onsite" => $page->onsite]);
      }
      // } else {
      //     return response()->json($code);
      // }
      return response()->json(["onsite" => $page->onsite]);
    } catch (Exception $err) {
      return response()->json(["error" => $err->getMessage()], 500);
    }
  }

  public function pasteKey(Request $request)
  {
    //rule here
    $ms = Mission::where([
      ["ip", $this->getUserIpAddr()],
      ["user_agent", $request->userAgent()],
      ["status", 0]
    ]);
    $msGet = ($ms)->get();
    if ($msGet->count() == 0) {
      return response()->json(["error" => "No mission"]);
    }
    $code = Code::where(["code" => $request->key, "status" => CodeStatusConstants::SENT])->get()->first();
    if (empty($code)) {
      return response()->json(["error" => "Not correct code"]);
    }

    DB::transaction(function () use ($ms, $code) {
      $ms->update(["status" => 1]);
      $code->status = CodeStatusConstants::USED;
      $code->save();
    });
    $info = Info::inRandomOrder()->first();
    return response()->json(["status" => "Correct code", "info" => $info]);
  }
}
