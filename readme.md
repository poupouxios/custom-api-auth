## Custom Api Auth

This package is responsible to override the default Auth of Laravel and authenticate the user through the API.

## Usage

* Include the repository in your `composer.json` file as below:
```
"repositories": [
    {
        "type": "git",
        "url": "git@github.com:poupouxios/facade-api.git"
    },
    {
      "type": "git",
    	"url": "https://github.com/poupouxios/custom-api-auth"
	  }
]
```
* Add in the require section of `composer.json` the below line (Check the tag versions to include the correct and latest one):
```
  ...
  "poupouxios/facade-api" : "~0.1",
  "poupouxios/custom-api-auth": "~0.1"
```
* In your `config\auth.php` add the below line in the providers array in a new row:
```
...
        'api_users' => [
            'driver' => 'externalauthapi',
            'model' => CustomApiAuth\Models\ApiUser::class,
        ],
...
```
* The final result of the providers array will look like:
```
  'providers' => [
       'users' => [
           'driver' => 'eloquent',
           'model' => CustomApiAuth\Models\ApiUser::class,
       ],
       'api_users' => [
           'driver' => 'externalauthapi',
           'model' => CustomApiAuth\Models\ApiUser::class,
       ],
   ],
```
* After setting the provider array, change the `api` provider under `guards` array in `config/auth.php` and set it to be `api_users`.
* The final result will look like:
```
...
  'api' => [
      'driver' => 'session',
      'provider' => 'api_users',
  ],
...
```
* Finally, change the `defaults` options on top of `config/auth.php` to be like:
```
...
  'defaults' => [
      'guard' => 'api',
      'passwords' => '',
  ],
...
```
* In your `config/app.php` add the below line in `providers` array:
```
  CustomApiAuth\Providers\ExternalAuthApiServiceProvider::class
```
* The `ExternalAuthApiServiceProvider` provider will register a new `ExternalAuthApiUserProvider`, which will be used when an authentication is needed.
* In `App\Http\Kernel.php` add in the `$routeMiddleware` array the below middleware:
```
...
  'auth.token' => \CustomApiAuth\Middleware\AuthenticateWithToken::class,
...
```
* The above middleware will authenticate each request the user makes with the saved token through the API.
* The final step is to wrap all your routes that need to be authenticated from the API to have access. Below is an example of how to set it:
```
...
  Route::group(['middleware' => ["auth.token","auth:api"]],function (){
      Route::get('home', function () {
          return view('home.index');
      })->name("home");
  });
...
```
* This package has a dependency on another package which is https://github.com/poupouxios/facade-api. Follow the instructions of that package to set it correct in order for this package to work.
* You must implement the below API calls on your Auth Service project that will be used to authenticate your project:
  * ``` ApiWrapper::call("post",'auth/login',["ApiUserId" => $identifier]); ```
  * ``` ApiWrapper::call("get","user") ```
  * ``` ApiWrapper::call("get","auth/logout") ```
* That should be enough to enable the custom auth.

## Improvements

* Need to inject the custom model so that each project that adds this composer package to set its own User Model.
