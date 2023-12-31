<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Deposit;
use App\Http\Controllers\Controller;
use App\Notifications\DepositAlert;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

class AdminDeposits extends Controller
{
    public function deposits()
    {
        $deposits = Deposit::all();
        return view('admin.deposits.deposits', compact('deposits'));
    }

    public function add_deposit()
    {
        $users = User::where('admin', 0)->get();
        return view('admin.deposits.add-deposit', compact('users'));
    }

    public function storeDeposit(Request $request)
    {
        $request->validate([
            'from' => 'required',
            'amount' => 'required',
            'note' => 'nullable',
        ]);

        $deposit = new Deposit();
        $deposit->from = $request->from;
        $deposit->amount = $request->amount;
        $deposit->note = $request->note;
        $deposit->status = 1;
        $deposit->user_id = $request->user_id;
        $deposit->save();
        $account = Account::findOrFail($deposit->user_id);
        $account->balance += $request->amount;
        $account->save();
        //send mail
        $user = User::findOrFail($request->user_id);
        Notification::route('mail', $user->email)->notify(new DepositAlert($deposit));
        return redirect()->route('admin.deposits')->with('success', "Fu");

    }

    public function deleteDeposit($id)
    {
        $deposit = Deposit::findOrFail($id);
        $deposit->delete();
        return redirect()->back();
    }

}
