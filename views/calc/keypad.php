<?php 
use app\assets\CalcAsset;
CalcAsset::register($this);

$this->beginPage();
 ?>

<p> This is calculator </p>

<div class='calc container'>
	<div class='row'>
		<div class='disp-col col-sm-2'>
			<p id='disp' class='disp'>0.</p>
		</div>
	</div>
	<div class='row'>
		<div class='keypad col-sm-3'>
			<div class='row'>
				<button id='1' type='button' class='keypad-key btn btn-primary'>1</button>
				<button id='2' type='button' class='keypad-key btn btn-primary'>2</button>
				<button id='3' type='button' class='keypad-key btn btn-primary'>3</button>
				<button id='+' type='button' class='keypad-key btn btn-primary'>+</button>
				<button id='C' type='button' class='keypad-key btn btn-primary'>C</button>
			</div>
			<div class='row'>
				<button id='4' type='button' class='keypad-key btn btn-primary'>4</button>
				<button id='5' type='button' class='keypad-key btn btn-primary'>5</button>
				<button id='6' type='button' class='keypad-key btn btn-primary'>6</button>
				<button id='-' type='button' class='keypad-key btn btn-primary'>-</button>
				<button id='negative' type='button' class='keypad-key btn btn-primary'>(-)</button>

			</div>
			<div class='row'>
				<button id='7' type='button' class='keypad-key btn btn-primary'>7</button>
				<button id='8' type='button' class='keypad-key btn btn-primary'>8</button>
				<button id='9' type='button' class='keypad-key btn btn-primary'>9</button>
				<button id='x' type='button' class='keypad-key btn btn-primary'>x</button>
			</div>
			<div class='row'>
				<button id='0' type='button' class='keypad-key btn btn-primary'>0</button>
				<button id='decPoint' type='button' class='keypad-key btn btn-primary'>.</button>
				<button id='result' type='button' class='keypad-key btn btn-primary'>=</button>
				<button id='/' type='button' class='keypad-key btn btn-primary'>/</button>
			</div>
		</div>
	</div>
</div>
