<?php

class GoauthController extends \BaseController {
  /**
   * Google Sign in
   *
   * @return GoogleAuth
   */
  public function signin(){
    $Auth = App::make('Maer\GoogleAuth\GoogleAuth');
    // This call will redirect the user to Googles Auth screen
    return $Auth->authorize();
  }

  /**
   * Account sign in form processing.
   *
   * @return Redirect
   */
  public function callback(){
    // Get the instance of GoogleAuth
    $Auth = App::make('Maer\GoogleAuth\GoogleAuth');
    // If the authorization fails, this method will return null.
    // Now it's up to you to decide what to do with the user object.
    $User = $Auth->callback();
    $token = $Auth->getAccessToken();
    if($User){
      try{
        $user = Sentry::findUserByLogin($User->email);
      }catch(Cartalyst\Sentry\Users\UserNotFoundException $e){
        //Create the user if not exist
        $user = Sentry::register(array(
          'email'    => $User->email,
          'password' => $User->uid,
          'first_name' => $User->firstName,
          'last_name' => $User->lastName,
          'activated' => true
        ));

      }
      //Login the user
      Sentry::login($user, true);
      // Get the page we were before
      $redirect = Session::get('loginRedirect', 'account');
      Session::forget('loginRedirect');
      return Redirect::to($redirect)->with('success', Lang::get('auth/message.signin.success'));
    }else{
      return Redirect::route('signin')->with('error', 'The email domain is not allowed');
    }

  }
}
