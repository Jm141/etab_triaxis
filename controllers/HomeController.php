<?php
/**
 * Home Controller
 */

class HomeController extends Controller {
    
    public function index() {
        if (Session::has('user_id')) {
            $this->redirect('/tabulation/dashboard');
        } else {
            $this->redirect('/tabulation/login');
        }
    }
}



