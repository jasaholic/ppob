<?php

namespace App\Http\Controllers\Member;

use App\AppModel\Message;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use DB;
use Auth;
use Mail;
use App\User;

class MessageController extends Controller
{
    
    public function index()
    {
        $messages = Message::select('messages.*','users.name','users.email','users.phone')->leftjoin('users','messages.from','users.id')->orderBy('messages.updated_at', 'DESC')->where('type','direct')->where('messages.from',Auth::user()->id)->paginate(12);
        return view('member.messages.index', compact('messages'));
    }

    public function store(Request $request)
    {
      DB::beginTransaction();
      try{
        $type       = $request->type;
        $id_induk   = $request->id_induk;
        $id_balas   = $request->id_balas;
        $from       = $request->from;
        $to         = $request->to;
        $isipesan   = addslashes(trim($request->isipesan));
        $isisubject = addslashes(trim($request->isisubject));
        
        $simpan = DB::table('messages')
        ->insertGetId([
          'from'         => $from,
          'to'           => $to,
          'type'         => $type,
          'id_reply'     => ($type == 'reply')?''.$id_balas.'':'-',
          'induk_message'=> $id_induk,
          'subject'      => $isisubject,
          'message'      => $isipesan,
          'status'       => '0',
          'created_at'   => date('Y-m-d H:i:s'),
          'updated_at'   => date('Y-m-d H:i:s'),
        ]);
        
        
        Message::where('id', $simpan)->update(['induk_message' => $simpan]);
        
        $getUser = User::find($from);
        
        $data = ['id_from' => $from, 'name_from' => $getUser->name, 'subject' =>$isisubject, 'isipesan'=>$isipesan, 'created_at' => date('Y-m-d H:i:s'), ];
        
        DB::commit();
        return redirect(url('/member/messages'))->with('alert-success', 'Berhasil Mengirim Pesan');
      }catch(\Exception $e){
        \DB::rollBack();
      }
    }
    
    public function reply(Request $request)
    {
        $type     = $request->type;
        $id_induk = $request->id_induk;
        $id_balas = $request->id_balas;
        $from     = $request->from;
        $to       = $request->to;
        $isipesan = addslashes(trim($request->isipesan));
        
        DB::table('messages')
        ->insert([
          'from'         => $from,
          'to'           => $to,
          'type'         => $type,
          'id_reply'     => ($type == 'reply')?''.$id_balas.'':'-',
          'induk_message'=> $id_induk,
          'subject'      => '-',
          'message'      => $isipesan,
          'status'       => '0',
          'created_at'   => date('Y-m-d H:i:s'),
          'updated_at'   => date('Y-m-d H:i:s'),
        ]);
        
        
        DB::table('messages')
              ->where('id', $id_induk)
              ->update(['status' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
              
        // return redirect()->back()->with('alert-success', 'Berhasil Membalas Pesan Masuk');
        
        return redirect(url('/member/messages'))->with('alert-success', 'Berhasil Membalas Pesan Masuk');
    }

    public function show($id)
    {
        DB::table('messages')
              ->whereNotIn('id', [$id])
              ->where('to', Auth::user()->id)
              ->where('induk_message', $id)
            //   ->update(['status' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
              ->update(['status' => 1]);
        
        $chckmessage = Message::where('induk_message', $id)->whereNotIn('id', [$id])->where('status', 0)->count();
        
        if($chckmessage == 0){
            DB::table('messages')
              ->where('id', $id)
            //   ->update(['status' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
              ->update(['status' => 1]);
        }
        
        $induk_messages = Message::select('messages.*','users.name','users.email','users.phone','users.image')->leftjoin('users','messages.from','users.id')->orderBy('messages.updated_at', 'ASC')->where('messages.id',$id)
        ->where(function($q){
          $q->where('messages.from',auth()->user()->id)
          ->orwhere('messages.to',auth()->user()->id);
        })->firstOrFail();
        
        $messages = Message::select('messages.*','users.name','users.email','users.phone','users.image')->leftjoin('users','messages.from','users.id')->orderBy('messages.updated_at', 'ASC')->where('messages.induk_message',$id)
        ->where(function($s){
          $s->where('messages.from',auth()->user()->id)
          ->orwhere('messages.to',auth()->user()->id);
        })->get();
        
        return view('member.messages.show', compact('induk_messages','messages'));
    }
    
    public function destroy($id)
    {
        $messages = Message::findOrFail($id);
        $messages->delete();
        
        DB::table('messages')
              ->where('induk_message', $id)
              ->delete();
              
        return redirect()->back()->with('alert-success', 'Berhasil Menghapus Pesan Masuk');
    }
}