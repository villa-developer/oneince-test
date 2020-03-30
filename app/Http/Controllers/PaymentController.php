<?php

namespace App\Http\Controllers;

use App\Payment;
use App\User;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|min:1|numeric'
        ]);

        $user = User::with('account')->findOrFail($request->user_id);

        if($request->amount > $user->account->balance) {
            abort(422, "You don't have enough money, add more money to your account");
        }

        $new_balance = $user->account->balance - $request->amount;
        $payment = Payment::create($request->all());
        $user->account()->update([
            'balance' => $new_balance
        ]);

        return response()->json([
            'payment' => $payment,
            'account' => $user->account->fresh()
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'amount' => 'required|min:1|numeric'
        ]);

        $payment = Payment::findOrFail($id);
        $user = User::findOrFail($request->user_id);
        $diff = '';

        
        $new_balance = ($user->account->balance + $payment->amount) - $request->amount;
        if($new_balance < 0) {
            abort(422, "You cannot add a payment greater than the amount you have in your account");
        }
        $payment->amount = $request->amount;
        $payment->save();
        $user->account()->update([
            'balance' => $new_balance
        ]);

        return response()->json([
            'payment' => $payment,
            'account' => $user->account->fresh()
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $user = $payment->user;

        /**
         * Sumamos la cantidad eliminada
         */
        $new_balance = $user->account->balance + $payment->amount;

        $user->account->update(
            ['balance' => $new_balance]
        );

        $payment->delete();
        return response()->json($user->account->fresh());
    }
}
