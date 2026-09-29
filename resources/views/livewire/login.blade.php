<div class="bg-white shadow border-0 rounded border-light p-4 p-lg-5 w-100 fmxw-500">
    <div class="text-center text-md-center mb-4 mt-md-0">
       <h1 class="mb-0 h3">{{ __('Sign in') }}</h1>
    </div>

    <div wire:loading.flex wire:target="login">
        @include('includes.spinner')
    </div>

    <form wire:submit.prevent="login" class="mt-4">

        @include('includes.alert')

       <!-- Form -->
       <div class="form-group mb-4">
          <label for="username">{{ __('Your Username') }}</label>
          <div class="input-group">
             <span class="input-group-text" id="basic-addon1">
               <div class="icon icon-xs text-gray-600">
                   <i class="fa-solid fa-user"></i>
               </div>
             </span>
             <input wire:model="username" type="text" class="form-control" placeholder="Username" id="username" autofocus required>
          </div>
       </div>
       <!-- End of Form -->
       <div class="form-group">
          <!-- Form -->
          <div class="form-group mb-4">
          <label for="password">{{ __('Your Password') }}</label>
             <div class="input-group">
                <span class="input-group-text" id="basic-addon2">
                   <svg class="icon icon-xs text-gray-600" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                      <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"></path>
                   </svg>
                </span>
                <input wire:model="password" type="password" placeholder="Password" class="form-control" id="password" required>
             </div>
          </div>
          <!-- End of Form -->
          <div class="d-flex justify-content-between align-items-top mb-4">
             <div class="form-check">
                <input wire:model="remember" class="form-check-input" type="checkbox" value="true" id="remember"> 
                <label class="form-check-label mb-0" for="remember">{{ __('Remember me') }}</label>
            </div>
             {{-- <div><a href="./forgot-password.html" class="small text-right">Lost password?</a></div> --}}
          </div>
       </div>


       <div class="d-grid"><button type="submit" class="btn btn-gray-800">{{ __('Sign in') }}</button></div>
    </form>

 </div>
