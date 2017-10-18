<?php
echo <<<EOT

<!DOCTYPE html>
<html >
<head>
  <meta charset="UTF-8">
  <title>CSS3 Timeline</title>
  
  
  
<style>
@import url(http://fonts.googleapis.com/css?family=Noto+Sans);
body {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 5%;
  font-size: 100%;
  font-family: "Noto Sans", sans-serif;
  color: black;
  background:url(/views/images/tmp/main.png) no-repeat bottom;
  background-size: contain;
}

h2 {
  margin: 20 0 0 0;
  font-size: 1.5em;
  letter-spacing: 2px;
  text-transform: uppercase;
}

/* -------------------------------------
 * timeline
 * ------------------------------------- */
#timeline {
  list-style: none;
  margin: 0 0 30px 120px;
  padding-left: 30px;
  border-left: 8px solid #eee9dc;

}
#timeline li {
  margin: 20px 0;
  position: relative;
}
#timeline p {
  margin: 0 0 10px;
}

.date {
  margin-top: -10px;
  top: 50%;
  left: -158px;
  font-size: 1.2em;
  line-height: 20px;
  position: absolute;
}

.circle {
  margin-top: -10px;
  top: 50%;
  left: -44px;
  width: 10px;
  height: 10px;
  background: #48b379;
  border: 5px solid #eee9dc;
  border-radius: 50%;
  display: block;
  position: absolute;
}

.relative label {
  cursor: auto;
  transform: translateX(42px);
}
.relative .circle {
  background: #f98262;
}
.content {
  max-height: 180px;
  border-color: #eee9dc;
  margin-right: 20px;
  transform: translateX(20px);
  transition: max-height 0.4s linear, border-color 0.5s linear, transform 0.2s linear;
}
.content p {
  max-height: 200px;
  color: #0c08cc;
  transition: color 0.3s linear 0.3s;
}

.content p span {
  display:inline-block;


  min-width: 150px;
}

label {
  font-size: 1.3em;
  cursor: pointer;
  top: 20px;
  margin-left:20px;
  transition: transform 0.2s linear;
}
</style>


</head>

<body>
  <h2>武蔵野様　ご来訪Agenda</h2>
<marquee><font size=+3 color=#0c08cc>成井 正己様、土井 俊明様　大連へよこそう！</font></marquee>
<ul id='timeline'>
  <li class='work'>
    <div class="relative">
      <label>IBM Dalian Studio Tour</label>
      <span class='date'>15:00~15:40</span>
      <span class='circle'></span>
    </div>
    <div class='content'>
      <p>
        <span>IBM Studio</span> CIC TBD IBM Studio Speaker
      </p>
    </div>
  </li>
  <li class='work'>
    <div class="relative">
      <label>休憩</label>
      <span class='date'>15:40~15:50</span>
      <span class='circle'></span>
    </div>
    <div class='content'>
      <p>
		<span>IBM Studio</span>
      </p>
    </div>
  </li>
  <li class='work'>
    <div class="relative">
      <label>Opening/Welcome speech</label>
      <span class='date'>15:50~15:55</span>
      <span class='circle'></span>
    </div>
    <div class='content'>
      <p>
		<span>IBM Studio CBC</span>IBM-J 今西
      </p>
    </div>
  </li>
  <li class='work'>
    <div class="relative">
      <label>武蔵野HD Speech</label>
      <span class='date'>15:55~16:00</span>
      <span class='circle'></span>
    </div>
    <div class='content'>
      <p>
		<span>IBM Studio CBC</span>武蔵野HD 成井様 新基幹システム プロジェクト PM
	  </p>
    </div>
  </li>
  <li class='work'>
    <div class="relative">
      <label>CICセンターのご紹介</label>
      <span class='date'>16:00~16:15</span>
      <span class='circle'></span>
    </div>
    <div class='content'>
      <p>
		<span>IBM Studio CBC</span>CIC 雲　福和 Japan Cross Sector Leader
	  </p>
    </div>
  </li>
  <li class='work'>
    <div class="relative">
      <label>Distribution Japan New Capability Introduction</label>
      <span class='date'>16:15~16:25</span>
      <span class='circle'></span>
    </div>
    <div class='content'>
      <p>
		<span>IBM Studio CBC</span>CIC 楊　伝勝 Japan Distribution Delivery Leader
	  </p>
    </div>
  </li>
  <li class='work'>
    <div class="relative">
      <label>休憩</label>
      <span class='date'>16:35~16:45</span>
      <span class='circle'></span>
    </div>
    <div class='content'>
      <p>
		<span>IBM Studio CBC</span>
	  </p>
    </div>
  </li><li class='work'>
    <div class="relative">
      <label>IIP Introduction(Innovation Incubation PGM)</label>
      <span class='date'>16:45~17:25</span>
      <span class='circle'></span>
    </div>
    <div class='content'>
      <p>
		<span>IBM Studio CBC</span>CIC 馬　堃 IIP Program Leader
	  </p>
    </div>
  </li>
  <li class='work'>
    <div class="relative">
      <label>クロージング</label>
      <span class='date'>17:25~17:30</span>
      <span class='circle'></span>
    </div>
    <div class='content'>
      <p>
		<span>IBM Studio CBC</span>CIC 雲　福和 Japan Cross Sector Leader
	  </p>
    </div>
  </li>
    <li class='work'>
    <div class="relative">
      <label>写真撮影１</label>
      <span class='date'>17:30~17:35</span>
      <span class='circle'></span>
    </div>
    <div class='content'>
      <p>
		<span>IBM Studio CBC</span>IBM Studio皆様
	  </p>
    </div>
  </li>
</ul>

</body>
</html>

EOT;

?>