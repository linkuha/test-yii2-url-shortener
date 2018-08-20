<?php

use app\modules\shorten\models\Link;
use app\modules\shorten\Module;
use yii\helpers\Html;
use yii\helpers\Url;

// Set Title and Breadcrumbs
$this->title = 'Сокращатель ссылок';

//$this->registerCssFile('@web/css/loader2.css');
$this->registerCss('
div.loader {
	height: 16px;
	width: 16px;
	display: inline-block;
    background: #fff url("/img/load.gif") no-repeat center;
}
');
$this->registerJsFile("https://cdn.rawgit.com/zenorocha/clipboard.js/master/dist/clipboard.min.js", ['position' => yii\web\View::POS_HEAD ]);
$this->registerJs("
$(document).ready(function() {
	
	var fullURL = document.getElementById('full-url');
	var loader = document.querySelector('div.loader');
	var resultLinkBlock = document.querySelector('.shorten-link-block');
	
	lightInput = function() {
		setTimeout(function() {
			fullURL.style.borderColor = '';
		}, 2000);
	};
	
	$('#shorten-button').focus();
	$('#shorten-button').on('click', function(event) {
		event.preventDefault();
		
		
		if (fullURL.value === 'URL...' || !fullURL.value) {
			fullURL.style.borderColor = 'red';
			lightInput();
			return;
		}
		
		resultLinkBlock.classList.add('hidden');
		event.target.setAttribute('disabled', 'disabled');
		loader.classList.remove('hidden');
		
		$.ajax({
			type: 'POST',
			url: '" . Url::to(['/shorten/link/generate']) . "',
			data: {'full_url': fullURL.value},
			async: true,
			timeout: 2000,
			success: function(response) {
				if (!response) return;
				console.log(response);
				
				// todo check value exists
				$('#shorten-link').val('" . Url::toRoute('', true). "' + response.alias);
				event.target.removeAttribute('disabled');
				loader.classList.add('hidden');
				resultLinkBlock.classList.remove('hidden');
			}
		});
	});
	
	new ClipboardJS('.copy');
});
");
?>

<div class="shorten-link-index">

	<div class="container" style="margin:0 auto; text-align: center;">
		<p>Введите адрес ссылки, которую хотите укоротить</p>
	
		<div class="form-group">
			<?= Html::input('text', 'full-url', 'URL...',
				[
					'id' => 'full-url',
					'onfocus' => "if (this.value == 'URL...') {this.value = '';}",
					'onblur' => "if (this.value == '') {this.value = 'URL...';}",
					'style' => 'width:50%; border-radius:3px;'
					]
				) ?>
			<hr/>
			<?= Html::a('Укоротить',
					null,
					[
					'class' => 'btn btn-danger btn-sm',
					'id' => 'shorten-button'
				]); ?>
			<br/><br/>
			<div class="loader hidden">
			</div>
			<!--div class="css-loader hidden">
				<div class="load-inner load-one"></div>
				<div class="load-inner load-two"></div>
				<div class="load-inner load-three"></div>
			</div-->
			<div class="shorten-link-block hidden" style="padding: 15px; background: #71bc78; border-radius: 5px; cursor: pointer;">
			<p>Ваша ссылка</p>
			<?= Html::input('text', 'shorten-link', '',
				[
					'id' => 'shorten-link',
					'readonly' => true,
					'style' => 'width: 300px; text-align: center;'
					]
				) ?> [<a href=# class="copy" data-clipboard-target="#shorten-link">копировать</a>]
			</div>
		</div>
		
	</div>
</div>
