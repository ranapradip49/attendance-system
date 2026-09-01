<?php

session_start();

include "db/connect.php";


if(!isset($_SESSION['user_id'])){
    header("Location:index.php");
    exit();
}


$user_id=$_SESSION['user_id'];



$sql=$conn->prepare(

"SELECT 

users.name,
users.email,

profiles.*

FROM users

JOIN profiles

ON users.id = profiles.user_id

WHERE users.id=?"

);



$sql->bind_param(
"i",
$user_id
);


$sql->execute();


$data=$sql->get_result()->fetch_assoc();



?>


<!DOCTYPE html>

<html lang="ja">

<head>

<meta charset="UTF-8">

<title>プロフィール設定</title>


<style>

/* GOOGLE FONT */

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap');


*{

margin:0;

padding:0;

box-sizing:border-box;

font-family:'Poppins',Arial;

}



body{


min-height:100vh;

display:flex;

justify-content:center;

align-items:center;

background:#050505;

color:white;

overflow:auto;


}



/* RGB LIGHT EFFECT */

body::before{


content:"";

position:fixed;

width:500px;

height:500px;

background:#00ffff;

border-radius:50%;

filter:blur(180px);

top:-150px;

left:-150px;


animation:move1 8s infinite alternate;


}



body::after{


content:"";

position:fixed;

width:500px;

height:500px;

background:#ff00ff;

border-radius:50%;

filter:blur(180px);

bottom:-150px;

right:-150px;


animation:move2 8s infinite alternate;


}



@keyframes move1{


from{

transform:translate(0,0);

}


to{

transform:translate(250px,200px);

}


}



@keyframes move2{


from{

transform:translate(0,0);

}


to{

transform:translate(-250px,-200px);

}


}





.profile-box{


position:relative;

z-index:2;

width:450px;

padding:40px;

margin:30px;


background:rgba(0,0,0,0.75);


border-radius:25px;


border:2px solid cyan;


box-shadow:


0 0 20px cyan,

0 0 50px magenta;



backdrop-filter:blur(15px);



}





h1{


text-align:center;


font-size:35px;


margin-bottom:25px;


color:white;



text-shadow:


0 0 10px cyan,

0 0 30px cyan;



}




.user-info{


text-align:center;

margin-bottom:20px;


}



.user-info h3{


color:#00ff99;


text-shadow:

0 0 15px #00ff99;


}



.user-info p{


color:#ddd;

}




.profile-img{


display:block;

margin:20px auto;


width:160px;

height:160px;


border-radius:50%;


object-fit:cover;


border:5px solid cyan;


box-shadow:


0 0 20px cyan,

0 0 40px blue,

0 0 60px magenta;



}




label{


display:block;


margin-top:18px;


color:#00ffff;


font-weight:bold;


}



input{


width:100%;


padding:14px;


margin-top:8px;


background:#080808;


color:white;


border:1px solid cyan;


border-radius:12px;


outline:none;



}




input:focus{


box-shadow:


0 0 20px cyan;


border-color:#00ffff;


}




input[type="file"]{


color:#00ff99;


}




button{


width:100%;


padding:15px;


margin-top:25px;


border:none;


border-radius:50px;


font-size:18px;


font-weight:bold;


cursor:pointer;


transition:.3s;



}



.save{


background:#00ff99;


color:black;


box-shadow:


0 0 20px #00ff99,

0 0 40px #00ff99;



}



.next{


background:#ff00ff;


color:white;


box-shadow:


0 0 20px magenta,

0 0 40px magenta;



}



button:hover{


transform:scale(1.08);


}



a{


text-decoration:none;


}



</style>


</head>



<body>



<div class="profile-box">



<h1>
プロフィール設定
</h1>




<div class="user-info">


<h3>

名前：
<?php echo $data['name']; ?>

</h3>



<p>

メール：
<?php echo $data['email']; ?>

</p>



</div>





<img class="profile-img"

src="<?php

echo !empty($data['profile_image'])

? $data['profile_image']

: 'default.png';


?>">






<form action="save_profile.php"

method="POST"

enctype="multipart/form-data">





<label>

プロフィール画像

</label>


<input type="file"

name="profile_image">





<label>

生年月日

</label>


<input type="date"

name="birth_date"

value="<?php echo $data['birth_date']; ?>">




<label>

住所

</label>


<input type="text"

name="address"

value="<?php echo $data['address']; ?>">




<label>

入社日

</label>


<input type="date"

name="joined_date"

value="<?php echo $data['joined_date']; ?>">





<label>

国籍

</label>


<input type="text"

name="nationality"

value="<?php echo $data['nationality']; ?>">





<button class="save">

保存する

</button>




</form>





<a href="scan.php">

<button class="next">

次のページへ

</button>

</a>




</div>




</body>


</html>