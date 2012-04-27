## 設置手順

- CakePHP2.0 をダウンロードし通常通り設定

### Extjs Pluginの設定

> ROOT/plugins以下に Extjs Plugin を設置

	cd ROOT/plugins
	git clone https://github.com/kaz29/extjsplugin.git Extjs

> Extjs Pluginを読み込む

	vim APP/Config/bootstrap.php
	// 以下を追加
	CakePlugin::load('Extjs');

> ルーティングルールを追加

	vim APP/Config/routes.php
 	//　以下を追記
	Router::connect('/:plugin/:controller/:action.:ext');
	Router::connect('/:controller/:action.:ext');

### ExtJSの設置

> ExtJS 4.0.x をダウンロードして解凍 http://www.sencha.com/products/extjs/download?page=a
> 現状は, ExtJS 4.0.7でのみ動作検証しています。

 	// APP/webroot 以下に ExtJS用ディレクトリを作成
	$ cd APP/webroot
	$ mkdir -p resources/js/ux/css

	// ExtJS関連ファイルを APP/webroot/extにコピー
	$ cd [ExtJSを解凍したディレクトリ]

 	// ExtJSの基本ファイルをコピー

	$ cp resources APP/webroot/

 	// ステータスバー機能関連ファイルをコピー
	$ cp -r examples/ux/statusbar APP/webroot/resources/ux/
	$ cp -r examples/ux/css/CheckHeader.css APP/webroot/resources/ux/css/

### Bake

> 通常と同様に bake all して、対象のモデルを選択します。

	$ php lib/Cake/Console/cake.php bake all
	Welcome to CakePHP v2.0.0-RC3 Console
	--
	App : app
	Path: [path to your app]/app/
	--
	Bake All
	--
	Possible Models based on your current database:
	1. Post
	Enter a number from the list above,
	type in the name of another model, or 'q' to exit  
	[q] > 1

	// 使用するテンプレートを聞かれるので、extjsを選択します。(以下の例では1を選択)
	You have more than one set of templates installed.
	Please choose the template set you wish to use:
	--> 1. extjs
	2. default
	Which bake theme would you like to use? (1/2) 
	[1] > 1

 	// Bakeしたモデルをメニューに表示する設定 - APP/Config/ext_direct.phpに設定を記述

	$ vim APP/Config/ext_direct.php
	<?php
 
	Configure::write('ext_direct_models', array(
		'Post' => array(),
	));
