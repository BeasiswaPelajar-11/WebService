<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';

require '../includes/DBOperations.php';

$app = new \Slim\App;

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");

    return $response;
});

$app->post('/createMahasiswa', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('email', 'nama', 'asal_univ', 'jurusan', 'gender', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'password'), $request, $response)){

        $request_data = $request->getParsedBody(); 

        $email = $request_data['email'];
        $nama = $request_data['nama'];
        $asal_univ = $request_data['asal_univ']; 
        $jurusan = $request_data['jurusan'];
        $gender = $request_data['gender'];
        $tempat_lahir = $request_data['tempat_lahir'];
		$tanggal_lahir = $request_data['tanggal_lahir']; 
        $alamat = $request_data['alamat']; 
        $password = $request_data['password'];

        $hash_password = password_hash($password, PASSWORD_DEFAULT);

        $db = new DBOperations; 

        $result = $db->createMahasiswa($email, $nama, $asal_univ, $jurusan, $gender, $tempat_lahir, $tanggal_lahir, $alamat, $hash_password);
        
        if($result == USER_CREATED){

            $message = array(); 
            $message['error'] = false; 
            $message['message'] = 'Registrasi Berhasil.';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(201);

        }else if($result == USER_FAILURE){

            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'Some error occurred';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    

        }else if($result == USER_EXISTS){
            $message = array(); 
            $message['error'] = true; 
            $message['message'] = 'Email Mahasiswa Sudah Terdaftar.';

            $response->write(json_encode($message));

            return $response
                        ->withHeader('Content-type', 'application/json')
                        ->withStatus(422);    
        }
    }
    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);    
});

$app->get('/allMahasiswa', function(Request $request, Response $response){

    $db = new DBOperations; 

    $mahasiswa = $db->getAllMahasiswa();

    $response_data = array();

    $response_data['error'] = false; 
    $response_data['mahasiswa'] = $mahasiswa; 

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  

});

$app->get('/allBeasiswa', function(Request $request, Response $response){

    $db = new DBOperations; 

    $beasiswa = $db->getAllBeasiswa();

    $response_data = array();

    $response_data['error'] = false; 
    $response_data['beasiswa'] = $beasiswa; 

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);  

});

$app->get('/allSaved', function(Request $request, Response $response){

    $db = new DBOperations; 

    $saved = $db->getAllSaved();

    $response_data = array();

    $response_data['error'] = false; 
    $response_data['saved'] = $saved; 

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);
});

$app->get('/allApplied', function(Request $request, Response $response){

    $db = new DBOperations; 

    $applied = $db->getAllSaved();

    $response_data = array();

    $response_data['error'] = false; 
    $response_data['applied'] = $applied; 

    $response->write(json_encode($response_data));

    return $response
    ->withHeader('Content-type', 'application/json')
    ->withStatus(200);
});

$app->post('/login', function(Request $request, Response $response){

    if(!haveEmptyParameters(array('email', 'password'), $request, $response)){
        $request_data = $request->getParsedBody(); 

        $email = $request_data['email'];
        $password = $request_data['password'];
        
        $db = new DBOperations; 

        $result = $db->Login($email, $password);

        if($result == USER_AUTHENTICATED){
            
            $mahasiswa = $db->getMahasiswaByEmail($email);
            $response_data = array();

            $response_data['error']=false; 
            $response_data['message'] = 'Welcome';
            $response_data['mahasiswa']=$mahasiswa; 

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);    

        }else if($result == USER_NOT_FOUND){
            $response_data = array();

            $response_data['error']=true; 
            $response_data['message'] = 'Mahasiswa Tidak Terdaftar.';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);    

        }else if($result == USER_PASSWORD_DO_NOT_MATCH){
            $response_data = array();

            $response_data['error']=true; 
            $response_data['message'] = 'Email atau Password Salah.';

            $response->write(json_encode($response_data));

            return $response
                ->withHeader('Content-type', 'application/json')
                ->withStatus(200);  
        }
    }

    return $response
        ->withHeader('Content-type', 'application/json')
        ->withStatus(422);    
});

function haveEmptyParameters($required_params, $request, $response){
    $error = false; 
    $error_params = '';
    $request_params = $request->getParsedBody(); 

    foreach($required_params as $param){
        if(!isset($request_params[$param]) || strlen($request_params[$param])<=0){
            $error = true; 
            $error_params .= $param . ', ';
        }
    }

    if($error){
        $error_detail = array();
        $error_detail['error'] = true; 
        $error_detail['message'] = 'Required parameters ' . substr($error_params, 0, -2) . ' are missing or empty';
        $response->write(json_encode($error_detail));
    }
    return $error; 
}

// $app->POST('/addSaved', function(Request $request, Response $response) use ($app){

//     if(!haveEmptyParameters(array('judul', 'deskripsi'), $request, $response)){

//         $request_data = $request->getParsedBody(); 

//         $judul = $request_data['judul'];
//         $deskripsi = $request_data['deskripsi'];

//         $db = new DBOperations; 

//         $result = $db->insertSaved($judul, $deskripsi);
        
//         if($result == SAVED_COMPLETED){

//             $message = array();
//             $message['error'] = false; 
//             $message['message'] = 'Data Disimpan.';

//             $response->write(json_encode($message));

//             return $response
//                         ->withHeader('Content-type', 'application/json')
//                         ->withStatus(201);

//         }else if($result == SAVED_FAILURE){

//             $message = array(); 
//             $message['error'] = true; 
//             $message['message'] = 'saad';

//             $response->write(json_encode($message));

//             return $response
//                         ->withHeader('Content-type', 'application/json')
//                         ->withStatus(422);    

//         }
//     }
//     return $response
//         ->withHeader('Content-type', 'application/json')
//         ->withStatus(422);

// });

$app->run();
