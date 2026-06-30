<!DOCTYPE html>
<html>
<head>

<title>Attendance Action</title>

<style>
body {
    background: #222;
    color: white;
    text-align: center;
    font-family: Arial;
    padding: 20px;
}

/* Container */
.profile-box{
    background:#333;
    padding:20px;
    border-radius:15px;
    width:300px;
    margin:20px auto;
}

/* Photo upload */
.photo-box{
    width:120px;
    height:120px;
    border:2px dashed white;
    border-radius:50%;
    margin:0 auto 15px;
    display:flex;
    align-items:center;
    justify-content:center;
    overflow:hidden;
    cursor:pointer;
}

.photo-box img{
    width:100%;
    height:100%;
    object-fit:cover;
}

/* Inputs */
.info {
    display:flex;
    flex-direction:column;
    gap:10px;
}

.info input{
    padding:10px;
    border-radius:8px;
    border:none;
    outline:none;
}

/* Buttons */
.btn {
    display: block;
    margin: 15px auto;
    width: 250px;
    padding: 15px;
    border-radius: 50px;
    background: white;
    color: black;
    text-decoration: none;
    font-size: 18px;
}

</style>

</head>

<body>

<p><?php echo date("Y-m-d H:i:s"); ?></p>

<!-- PROFILE SECTION -->
<div class="profile-box">

    <!-- Photo Upload -->
    <label class="photo-box">
        <input type="file" accept="image/*" hidden onchange="loadImage(event)">
        <img id="preview" src="" alt="Upload Photo">
        <span id="text">＋</span>
    </label>

    <!-- Info Fields -->
    <div class="info">
        <input type="text" placeholder="Name">
        <input type="text" placeholder="Class">
        <input type="number" placeholder="Age">
        <input type="text" placeholder="Address">
    </div>

</div>

<!-- ACTION BUTTONS -->
<a class="btn" href="confirm.php?code=&type=出勤">出勤</a>
<a class="btn" href="confirm.php?code=&type=休憩入り">休憩入り</a>
<a class="btn" href="confirm.php?code=&type=休憩戻り">休憩戻り</a>
<a class="btn" href="confirm.php?code=&type=退勤">退勤</a>

<script>
function loadImage(event){
    const img = document.getElementById("preview");
    const text = document.getElementById("text");

    img.src = URL.createObjectURL(event.target.files[0]);
    text.style.display = "none";
}
</script>

</body>
</html>