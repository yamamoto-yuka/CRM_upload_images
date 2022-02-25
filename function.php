<?php

class Users{
 private $host ="localhost";
 private $username ="root";
 private $password ="root";
 private $dbName ="CRM_project2";
 private $port = 8889;

 //Database Connection
 public $conn;

 public function __construct(){
     //$this = object
    $this->conn = new mysqli($this->host, $this->username, $this->password,$this->dbName, $this->port);
    
    if(mysqli_connect_error()){
        trigger_error('Error in DB'. mysqli_connect_error());
    }else{
        return $this->conn;
    }
 }

 //Check if you logged it. : Using in index.php
 public function check_login(){
    //  Stored the data in sever side

    // Informaiton of the session is stored assoxiative array
    // How to store :　$_SESSION['KEY] = value
    // Varianle ＝ $_SESSION['KEY]　can get the data 
     if(isset($_SESSION['user_id'])){
        
        //  Assign user_id as session data
        $id = $_SESSION['user_id'];
        $sql = "SELECT * FROM users WHERE user_id = '$id' LIMIT 1";

        $result = $this->conn->query($sql);

        if($result && $result->num_rows>0){
            $user_data =$result->fetch_assoc();
            return $user_data;
        }
     }else{
         header("Location: login.php");
         die;
     }
 }


//  Check that the information matches : Using in login.php
 public function login(){
        //Get the data using post method 
        $user_name = $_POST['user_name'];
        $password = $_POST['password'];

        if(!empty($user_name) && !empty($password) && !is_numeric($user_name)){
            $sql = "SELECT * FROM users WHERE user_name = '$user_name' LIMIT 1";
            $result = $this->conn->query($sql);
            
            if($result){
                if($result->num_rows>0){
                    $user_data =$result->fetch_assoc();

                    if($user_data['password'] == $password){
                        $_SESSION['user_id'] = $user_data['user_id'];
                        header("Location: contact.php");
                        die;
                    }
                }  
            }
            else{
                echo "Worong username or password!";
            }
        }else{
            echo "Please enter your username and password!";
        }
 }


//  Insert the information into DB : Using in signup.php
 public function signup(){
     $user_name = $_POST['user_name'];
     $password = $_POST['password'];

     if(!empty($user_name) && !empty($password) && !is_numeric($user_name)){
         $user_id = $this->random_num(20);
         $sql = "INSERT INTO users(user_id,user_name,password) VALUES('$user_id','$user_name','$password')";
         $result = $this->conn->query($sql);
        //  if($result){
        //      echo "New record created successfully";
        //  }else{
        //      var_dump($user_id);
        //      echo "Error: ". $this->conn->error;
        //  }
        header("Location: login.php");
     }else{
         echo "Please enter every infoemation";
     }
     
 }

//  Create random number for user id  : Using in signup.php
 public function random_num($length){
    $text ='';
    if($length <5 ){
        $length = 5;
    }

    // rand(min, max)
    // You can get a random number between 4 to specified number
    // Set this random number as the length of user id
    $len = rand(4, $length);
    for ($i = 0; $i<$len; $i++){
        $text .=rand(0, 9);
    }
    return $text;

 }

// Add the contact record into DB  : Using in contact.php
 public function add(){
    $business_name = $_POST['business_name'];
    $contact_name = $_POST['contact_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $inquiry = $_POST['inquiry'];
    $product = $_POST['product'];

    
    // Image upload testing
    $targetDirectory = "imageFolder/";
    $targetFile = $targetDirectory  .basename($_FILES['imageUplpaded']['name']);
    $uploadOK = true;


    $imageName = $this->conn->real_escape_string($targetFile);
    echo '$imageName '.$imageName;
    if(getimagesize($_FILES['imageUplpaded']['tmp_name']) === false){
        echo '<p> Not a image!</p>';
        $uploadOK = false;
    }
    if($_FILES['imageUplpaded']['size'] > 1000000){
        echo '<p> Your image is too large. Must be less than 1MB</p>';
        $uploadOK = false;
    }
    if($uploadOK === false){
        echo '<p> Image failed tests.</p>';
    }
    else{
        if(move_uploaded_file($_FILES['imageUplpaded']['tmp_name'], $targetFile)){
                echo '<p>The File'.basename($_FILES['imageUplpaded']['name']).'has been uploaded.</p>';

                $sql = "INSERT INTO contact_info(business_name, contact_name, email, phone, inquiry,product,images) VALUES('$business_name', '$contact_name', '$email', '$phone', '$inquiry', '$product','$imageName') ";
    
                $result = $this->conn->query($sql);
        }
        if($result){
            echo "New record created successfully";
            header("Location: contact.php");
        }else{
            echo "Error: ".$this->conn->error;
        }
    }


 }




 
//  Show the contact record on the Browser : Using in contact.php
 public function viewDate(){
     $sql = "SELECT * FROM contact_info";    
     $result = $this->conn->query($sql);

     if($result->num_rows>0){
         $data = array();
         while($row = $result->fetch_assoc()){
            $data[] = $row;
         }
         return $data;
     }
     
 }



//  Delete the contact record from DB :Using in contact.php
 public function delete($id){   



     //Delete an image from the folder
    $sql1 = "SELECT contact_info.images FROM contact_info WHERE id = '$id' LIMIT 1";
    $result1 = $this->conn->query($sql1);
    $filename = $result1->fetch_assoc();
    if(file_exists($filename['images'])){
        unlink($filename['images']);
        echo 'File '.$filename['images'].' has been deleted';
    }else {
        echo 'Could not delete '.$filename['images'].', file does not exist';
      }
    


     //Delete every data from DB(My SQL)
     $sql2 = "DELETE FROM contact_info WHERE id = '$id' LIMIT 1";
     $result2 = $this->conn->query($sql2);

     if($result2){
         echo "The contact record had been deleted";
         header("Location: contact.php");
     }else{
         echo "Not able to delete";
     }
 }

 


 public function getRecordById($id){
    // display the record of the user by id
    $sql = "SELECT * FROM contact_info WHERE id = '$id' LIMIT 1";
    $result = $this->conn->query($sql);

    if($result){
        if($result->num_rows>0){
            $row = $result->fetch_assoc();
            return $row;
        }else{
            echo "We don't have an edit id";
        }
    }
 }

 
public function update($postData){
    $business_name = $_POST['business_name'];
    $contact_name = $_POST['contact_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $inquiry = $_POST['inquiry'];
    $product = $_POST['product'];
    $id = $_POST['id'];


    // Image update testing
    $targetFile = "imageFolder/" .basename($_FILES['imageUplpaded']['name']);
    $imageName = $this->conn->real_escape_string($targetFile);
    if(getimagesize($_FILES['imageUplpaded']['tmp_name']) === false){
        echo '<p> Not a image!</p>';
        $uploadOK = false;
    }
    if($_FILES['imageUplpaded']['size'] > 1000000){
        echo '<p> Your image is too large. Must be less than 1MB</p>';
        $uploadOK = false;
    }
    if($uploadOK === false){
        echo '<p> Image failed tests.</p>';
    }else{
    move_uploaded_file($_FILES['imageUplpaded']['tmp_name'], $targetFile);
    echo  $imageName;
    
    
    if(!empty($id) && !empty($postData)){
        $sql = "UPDATE contact_info SET business_name='$business_name',contact_name='$contact_name', email='$email', phone='$phone', inquiry= '$inquiry ', product= '$product', images='$imageName' WHERE id='$id' ";
        $result = $this->conn->query($sql);

        if($sql){
            header("Location: contact.php");
        }else{
            echo "Updata failed";
        }
    }
    }





}
















}