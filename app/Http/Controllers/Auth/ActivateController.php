<?php

/**
 * Copyright (C) 2017 Bynq.io B.V.
 * All Rights Reserved
 *
 * This file is part of the Dynq project.
 *
 * Content can not be copied and/or distributed without the express
 * permission of the author.
 *
 * @package  Dynq
 * @author   Yorick de Wid <y.dewid@calculatietool.com>
 */

namespace BynqIO\Dynq\Http\Controllers\Auth;

use BynqIO\Dynq\Models\User;
use BynqIO\Dynq\Models\Audit;
use BynqIO\Dynq\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Auth;
use DB;
use Newsletter;

class ActivateController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Activation Controller
    |--------------------------------------------------------------------------
    |
    |
    */

    /**
     * Instantiate a new activate controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');

        //
    }

    /**
     * Activate user account.
     *
     * @return Route
     */
    public function __invoke($token)
    {
        try {
            $user = User::where('reset_token', $token)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return redirect()
                ->route('signin')
                ->withErrors(__('core.invactivatelink'));
        }

        $user->confirmed_mail = DB::raw('NOW()');
        $user->reset_token = null;
        $user->active = true;
        $user->save();

        \ExampleRelationTemplate::setup($user->id);

        Newsletter::subscribe($user->email, [
            'FNAME' => $user->firstname,
            'LNAME' => $user->lastname
        ]);

        Audit::CreateEvent('auth.activate.success', 'Activated with: ' . Audit::UserAgent(), $user->id);

        Auth::login($user);

        return redirect()->route('dashboard');
    }

}
