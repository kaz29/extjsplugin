<?php 
$out = <<<EOT
<?php
	config('ext_direct');
	\$ext_direct_models = Configure::read('ext_direct_models');
	foreach(\$ext_direct_models as \$name => \$params):
    \$this->Html->script(Router::url(
      array(
        'controller'  => Inflector::tableize(\$name),
        'action'      => 'script'
      )
    ), array('inline' => false));
  endforeach;
?>
EOT;
echo $out;
?>
