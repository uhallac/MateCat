<?php

namespace API\App;

use API\V2\KleinController;
use Exceptions\ValidationError;
use Monolog\Handler\Curl\Util;
use Symfony\Component\Config\Definition\Exception\Exception;
use Users\Signup ;
use FlashMessage ;

class SignupController extends KleinController {

    public function create() {
        // TODO: filter input params
        $signup = new Signup( $this->request->param('user') );

        if ( $signup->valid() ) {
            $signup->process();
            $this->response->code( 200 ) ;
        }
        else {
            $this->response->code( 400 ) ;
            $this->response->json( array('error' => array(
                'message' => $signup->getError()
            )) ) ;
        }
    }

    public function confirm() {
        \Bootstrap::sessionStart();
        try {
            Signup::confirm( $this->request->param('token') ) ;
        }
        catch( ValidationError $e ) {
            FlashMessage::set('confirmToken', $e->getMessage(), FlashMessage::ERROR );
        }

        $this->response->redirect( $this->__flushWantedURL()  );
    }

    public function redeemProject() {
        \Bootstrap::sessionStart();
        $_SESSION['redeem_project'] = TRUE ;
        $this->response->code( 200 ) ;
    }

    public function authForPasswordReset() {
        try {
            Signup::passwordReset( $this->request->param('token') ) ;
        }
        catch( ValidationError $e ) {
            FlashMessage::set('passwordReset', $e->getMessage(), FlashMessage::ERROR );

            $this->response->redirect( \Routes::appRoot() ) ;
        }

        if ( !$this->response->isLocked() ) {
            FlashMessage::set('popup', 'passwordReset', FlashMessage::SERVICE );
            $this->response->redirect( \Routes::appRoot( ) ) ;
        }
    }

    public function resendEmailConfirm() {
        Signup::resendEmailConfirm( $this->request->param('email') ) ;
        $this->response->code( 200 );
    }

    public function forgotPassword() {
        Signup::forgotPassword( $this->request->param('email') ) ;
        $this->response->code( 200 );

    }

    protected function afterConstruct() {
    }

    private function __flushWantedURL() {
        $url = isset( $_SESSION['wanted_url'] ) ? $_SESSION['wanted_url'] : \Routes::appRoot();
        unset($_SESSION['wanted_url']) ;
        return $url ;
    }

}