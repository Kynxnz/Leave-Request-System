<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class NotificationController extends Controller
{
    public function index(){return view('notifications.index',['notifications'=>auth()->user()->notifications()->latest()->paginate(20)]);}
    public function readAll(){auth()->user()->unreadNotifications->markAsRead();return back()->with('success','All notifications marked as read.');}
    public function read(string $id){$n=auth()->user()->notifications()->whereKey($id)->firstOrFail();$n->markAsRead();return redirect($n->data['url']??route('dashboard'));}
}
