Auth::check() defers to Auth::user(). It's been that way since as long as I can remember.

In other words, Auth::check() calls Auth::user(), gets the result from it, and then checks to see if the user exists. The main difference is that it checks if the user is null for you so that you get a boolean value.

This is the check function:

public function check()
{
    return ! is_null($this->user());
}
As you can see, it calls the user() method, checks if it's null, and then returns a boolean value



If you just want to check if the user is logged in, Auth::check() is more correct.

Auth::user() will make a database call (and be slightly heavier) than Auth::check(), which should simply check the session.

Auth::guard('admin')->user()->email 
try this it worked for me.

