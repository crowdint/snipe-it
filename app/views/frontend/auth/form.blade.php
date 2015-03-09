<form method="post" action="{{ route('signin') }}" class="form-horizontal">
    <!-- CSRF Token -->
    <input type="hidden" name="_token" value="{{ csrf_token() }}" />

        <div class="form-group">
            <label class="col-md-6 control-label"></label>
                <div class="col-md-5">
                    <br><a href="{{ route('forgot-password') }}" class="btn btn-link">I forgot my password</a>
                </div>
            </div>

        <!-- Email -->
        <div class="form-group{{ $errors->first('email', ' error') }}">
            <label for="email" class="col-md-3 control-label">Email</label>
                <div class="col-md-5">
                    <input class="form-control" type="email" name="email" id="email" value="{{{ Input::old('email') }}}" />
                    {{ $errors->first('email', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                </div>
        </div>

        <!-- Password -->
        <div class="form-group{{ $errors->first('password', ' error') }}">
            <label for="password" class="col-md-3 control-label">Password</label>
                <div class="col-md-5">
                    <input class="form-control" type="password" name="password" id="password" value="{{{ Input::old('password') }}}" />
                    {{ $errors->first('password', '<br><span class="alert-msg"><i class="fa fa-times"></i> :message</span>') }}
                </div>
        </div>

         <div class="field-box">
            <label class="col-md-3 control-label checkbox-inline"></label>
              <input type="checkbox" name="remember-me" id="remember-me" value="1" /> Remember me
              <a href="{{ url('/auth/google-auth') }}" class="g-signin" style="margin-left: 50px;" id="signInButton">Sign in with Google</a>
            </label>
        </div>

        <!-- Form actions -->
            <div class="form-group">
            <label class="col-md-6 control-label"></label>
                <div class="col-md-5">
                    <a class="btn btn-link" href="{{ route('home') }}">Cancel</a>
                    <button type="submit" class="btn btn-success"><i class="fa fa-ok icon-white"></i> Sign in</button>
                </div>
            </div>





</form>
