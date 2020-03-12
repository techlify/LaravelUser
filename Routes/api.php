<?php

/* Roles */
Route::get("roles", "RoleController@index")
    ->middleware("TechlifyAccessControl:role_read");
Route::post("roles", "RoleController@store")
    ->middleware("TechlifyAccessControl:role_create");
Route::put("roles/{id}", "RoleController@update")
    ->middleware("TechlifyAccessControl:role_update");
Route::delete("roles/{id}", "RoleController@destroy")
    ->middleware("TechlifyAccessControl:role_delete");
Route::get("roles/{id}", "RoleController@show")
    ->middleware("TechlifyAccessControl:role_read");
Route::patch("roles/{role}/permissions/{permission}/add", "RoleController@addPermission")
    ->middleware("TechlifyAccessControl:role_permission_add");
Route::patch("roles/{role}/permissions/{permission}/remove", "RoleController@removePermission")
    ->middleware("TechlifyAccessControl:role_permission_remove");

/* Permissions */
Route::resource("permissions", "\Modules\LaraveUser\Http\Controllers\PermissionController");

/* Users */
Route::get("users", "UserController@index")
    ->middleware("TechlifyAccessControl:user_read");
Route::post("users", "UserController@store")
    ->middleware("TechlifyAccessControl:user_create");
Route::put("users/{id}", "UserController@update")
    ->middleware("TechlifyAccessControl:user_update");
Route::delete("users/{id}", "UserController@destroy")
    ->middleware("TechlifyAccessControl:user_delete");
Route::get("users/{id}", "UserController@show")
    ->middleware("TechlifyAccessControl:user_read");
Route::patch("users/{id}/enable", "\Modules\LaraveUser\Http\Controllers\UserController@enable")
    ->middleware("TechlifyAccessControl:user_enable");
Route::patch("users/{id}/disable", "\Modules\LaraveUser\Http\Controllers\UserController@disable")
    ->middleware("TechlifyAccessControl:user_disable");

Route::put("users/profile/update", "UserController@updateCurrentUserProfile");

Route::post('/user/login', "\Modules\LaraveUser\Http\Controllers\SessionController@login");
Route::post('/user/logout', "\Modules\LaraveUser\Http\Controllers\SessionController@destroy");
Route::get('/user/current', "\Modules\LaraveUser\Http\Controllers\UserController@currentUser");
Route::patch("user/current/update-password", "\Modules\LaraveUser\Http\Controllers\UserController@user_password_change_own");

/* Company Sign Up */
Route::post("company-sign-up", "UserController@companySignUp");

/* Forgot Password */
Route::post("forgot-password", "UserController@forgotPassword");
