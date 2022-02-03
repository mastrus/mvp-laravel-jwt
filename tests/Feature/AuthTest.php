<?php

namespace Tests\Feature;

use App\Http\Controllers\AuthController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * @todo creare dei sotto metodi per il login e la creazione utente
 */
class AuthTest extends TestCase
{

    /**
     * costanti utente
     */
    const
        NAME = 'panco2',
        EMAIL = 'panco2@panco.net',
        PASSWORD = '123456f';

    /**
     * testo la creazione degli utenti
     *
     * @return void
     */
    public function testRegister()
    {
        $response = $this->json('POST', '/api/auth/register', [
            'name'  =>  self::NAME,
            'email'  =>  $email = rand().time().self::EMAIL,
            'password'  =>  self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ]);

        //Write the response in laravel.log
        Log::info('test Register ', [$response->getContent()]);

        $response->assertStatus(Response::HTTP_CREATED);
        $response->assertJson([
            AuthController::REGISTER_KEY => AuthController::REGISTER_MESSAGE
        ]);
        $this->assertArrayHasKey(AuthController::REGISTER_USER_KEY,$response->json());

        // Delete users
        User::where('email',$email)->delete();
    }

    /**
     * testo la creazione di tue utenti identici
     *
     * @return void
     */
    public function testRegisterSameUser(){

        $response = $this->json('POST', '/api/auth/register', [
            'name'  =>  self::NAME,
            'email'  =>  self::EMAIL,
            'password'  =>  self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ]);

        //Write the response in laravel.log
        Log::info('test Register same user ', [$response->getContent()]);

        $response = $this->json('POST', '/api/auth/register', [
            'name'  =>  self::NAME,
            'email'  =>  self::EMAIL,
            'password'  =>  self::PASSWORD,
            'password_confirmation' => self::PASSWORD,
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $this->assertEquals('"{\"email\":[\"The email has already been taken.\"]}"', $response->getContent());

    }


    /**
     * testo il login
     *
     * @return void
     */
    public function testLogin()
    {
        // Creating Users
        User::create([
            'name' => self::NAME,
            'email'=> $email = rand().time().self::EMAIL,
            'password' => bcrypt(self::PASSWORD)
        ]);

        // Simulated landing
        $response = $this->json('POST','/api/auth/login',[
            'email' => $email,
            'password' => self::PASSWORD,
        ]);

        //Write the response in laravel.log
        Log::info('test Login', [$response->getContent()]);

        // Determine whether the login is successful and receive token
        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'token_type' => 'bearer'
        ]);

        // Delete users
        User::where('email',$email)->delete();
    }

    /**
     * verifico i dati dell'utente
     *
     * @return void
     */
    public function testMe(){

        // Creating Users
        User::create([
            'name' => self::NAME,
            'email'=> $email = rand().time().self::EMAIL,
            'password' => bcrypt(self::PASSWORD)
        ]);

        // Simulated login
        $response = $this->json('POST','/api/auth/login',[
            'email' => $email,
            'password' => self::PASSWORD,
        ]);

        $token = $response->json('access_token');

        //mi collego per recuperare i dati utente
        $response = $this->json('GET','/api/auth/me',[
            'Authorization' => 'Bearer ' . $token
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'email' => $email,
            'name' => self::NAME
        ]);
        $this->assertArrayHasKey('id', $response->json());

        // Delete users
        User::where('email',$email)->delete();
    }

    /**
     * testo il refresh del token e il richiamo dei dati
     *
     * @return void
     */
    public function testRefresh(){
        // Creating Users
        User::create([
            'name' => self::NAME,
            'email'=> $email = rand().time().self::EMAIL,
            'password' => bcrypt(self::PASSWORD)
        ]);

        // Simulated login
        $response = $this->json('POST','/api/auth/login',[
            'email' => $email,
            'password' => self::PASSWORD,
        ]);

        //Write the response in laravel.log
        Log::info('test refrest old token', [$response->getContent()]);

        $token = $response->json('access_token');

        //mi collego per recuperare i dati utente
        $response = $this->json('POST','/api/auth/refresh',[
            'Authorization' => 'Bearer ' . $token
        ]);

        //Write the response in laravel.log
        Log::info('test refrest new token', [$response->getContent()]);

        $newToken = $response->json('access_token');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'token_type' => 'bearer'
        ]);
        $this->assertFalse($token == $newToken);

        //mi collego per recuperare i dati utente
        $response = $this->json('GET','/api/auth/me',[
            'Authorization' => 'Bearer ' . $newToken
        ]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            'email' => $email,
            'name' => self::NAME
        ]);
        $this->assertArrayHasKey('id', $response->json());

        // Delete users
        User::where('email',$email)->delete();
    }

    /**
     * testo il logout
     *
     * @return void
     */
    public function testLogout(){
        // Creating Users
        User::create([
            'name' => self::NAME,
            'email'=> $email = rand().time().self::EMAIL,
            'password' => bcrypt(self::PASSWORD)
        ]);

        // Simulated login
        $response = $this->json('POST','/api/auth/login',[
            'email' => $email,
            'password' => self::PASSWORD,
        ]);

        $token = $response->json('access_token');

        //mi collego per recuperare i dati utente
        $response = $this->json('POST','/api/auth/logout',[
            'Authorization' => 'Bearer ' . $token
        ]);

        //Write the response in laravel.log
        Log::info('test logout', [$response->getContent()]);

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJson([
            AuthController::LOGOUT_KEY => AuthController::LOGOUT_MESSAGE
        ]);

        // Delete users
        User::where('email',$email)->delete();
    }

    /**
     * testo la chiamata con un token non valido
     *
     * @return void
     */
    public function testErrorToken(){

        $token = rand();

        //mi collego per recuperare i dati utente
        $response = $this->json('GET','/api/auth/me',[
            'Authorization' => 'Bearer ' . $token
        ]);

        //Write the response in laravel.log
        Log::info('test testErrorToken', [$response->getContent()]);;

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);

    }
}
