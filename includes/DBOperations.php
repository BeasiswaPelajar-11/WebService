<?php

    class DBOperations{

        private $con; 

        function __construct(){
            require_once dirname(__FILE__) . '/DBConnect.php';
            $db = new DBConnect; 
            $this->con = $db->connect(); 
        }

        public function createMahasiswa($email, $nama, $asal_univ, $jurusan, $gender, $tempat_lahir, $tanggal_lahir, $alamat, $password){
            if(!$this->isEmailExist($email)){
                 $stmt = $this->con->prepare("INSERT INTO mahasiswa (email, nama, asal_univ, jurusan, gender, tempat_lahir, tanggal_lahir, alamat, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                 $stmt->bind_param("sssssssss", $email, $nama, $asal_univ, $jurusan, $gender, $tempat_lahir, $tanggal_lahir, $alamat, $password);
                 if($stmt->execute()){
                     return USER_CREATED; 
                 }else{
                     return USER_FAILURE;
                 }
            }
            return USER_EXISTS; 
         }

         public function Login($email, $password){
            if($this->isEmailExist($email)){
                $hashed_password = $this->getUsersPasswordByEmail($email); 
                if(password_verify($password, $hashed_password)){
                    return USER_AUTHENTICATED;
                }else{
                    return USER_PASSWORD_DO_NOT_MATCH; 
                }
            }else{
                return USER_NOT_FOUND; 
            }
        }

        private function getUsersPasswordByEmail($email){
            $stmt = $this->con->prepare("SELECT password FROM mahasiswa WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($password);
            $stmt->fetch(); 
            return $password; 
        }

         private function isEmailExist($email){
            $stmt = $this->con->prepare("SELECT email FROM mahasiswa WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->store_result(); 
            return $stmt->num_rows > 0;  
        }

        public function getAllMahasiswa(){
            $stmt = $this->con->prepare("SELECT email, nama, asal_univ, jurusan, gender, tempat_lahir, tanggal_lahir, alamat FROM mahasiswa;");
            $stmt->execute(); 
            $stmt->bind_result($email, $nama, $asal_univ, $jurusan, $gender, $tempat_lahir, $tanggal_lahir, $alamat);
            $users = array(); 
            while($stmt->fetch()){ 
                $user = array(); 
                $user['email'] = $email; 
                $user['nama']=$nama;
                $user['asal_univ'] = $asal_univ;
                $user['jurusan'] = $jurusan; 
                $user['gender']=$gender;
                $user['tempat_lahir'] = $tempat_lahir;
                $user['tanggal_lahir'] = $tanggal_lahir;
                $user['alamat'] = $alamat;
                array_push($users, $user);
            }             
            return $users;
        }

        public function getMahasiswaByEmail($email){
            $stmt = $this->con->prepare("SELECT email, nama, asal_univ, jurusan, gender, tempat_lahir, tanggal_lahir, alamat FROM mahasiswa WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute(); 
            $stmt->bind_result($email, $nama, $gender, $asal_univ, $jurusan, $tempat_lahir, $tanggal_lahir, $alamat);
            $stmt->fetch(); 
            $user = array();
            $user['email'] = $email; 
            $user['nama']=$nama;
            $user['asal_univ'] = $asal_univ;
            $user['jurusan'] = $jurusan; 
            $user['gender']=$gender;
            $user['tempat_lahir'] = $tempat_lahir;
            $user['tanggal_lahir'] = $tanggal_lahir;
            $user['alamat'] = $alamat;
            return $user; 
        }

        public function getAllBeasiswa(){
            $stmt = $this->con->prepare("SELECT nama, deskripsi, nominal FROM beasiswa;");
            $stmt->execute(); 
            $stmt->bind_result($nama, $deskripsi, $nominal);
            $beasiswa = array(); 
            while($stmt->fetch()){ 
                $data = array();
                $data['nama']=$nama;
                $data['deskripsi'] = $deskripsi;
                $data['nominal'] = $nominal;
                array_push($beasiswa, $data);
            }             
            return $beasiswa;
        }

        public function getAllSaved(){
            $stmt = $this->con->prepare("SELECT nama, deskripsi FROM saved;");
            $stmt->execute(); 
            $stmt->bind_result($nama, $deskripsi);
            $saved = array(); 
            while($stmt->fetch()){ 
                $data = array();
                $data['nama']=$nama;
                $data['deskripsi'] = $deskripsi;
                array_push($saved, $data);
            }             
            return $saved;
        }

        public function getAllApplied(){
            $stmt = $this->con->prepare("SELECT nama, deskripsi FROM applied;");
            $stmt->execute(); 
            $stmt->bind_result($nama, $deskripsi);
            $applied = array(); 
            while($stmt->fetch()){ 
                $data = array();
                $data['nama']=$nama;
                $data['deskripsi'] = $deskripsi;
                array_push($applied, $data);
            }             
            return $applied;
        }

        // public function insertSaved($judul, $deskripsi){
        //     $stmt = $this->con->prepare("INSERT INTO saved (judul, deskripsi) VALUES (?, ?)");
        //     $stmt->bind_param("ss", $judul, $deskripsi);
        //     if($stmt->execute()){
        //         return SAVED_COMPLETED; 
        //     }else{
        //         return SAVED_FAILURE;
        //     }
        // }
    }