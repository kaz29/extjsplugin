<?php
class DirectBehavior extends ModelBehavior {
	public function getDirectSettings(&$Model)
	{
		if ( !isset($Model->directSettings) ) {
			return array();
		}
		return $Model->directSettings;
	}
	
	/**
	 * ExtDirect用のレスポンスデータを生成する
	 *
	 * @param &$Model
	 * @param	boolean $result		処理結果
	 * @param	array 	$data			レスポンスデータ
	 * @access public
	 * @return void
	 * @author Kaz Watanabe
	 **/
	public function makeDirectResponce(&$Model, $result, $data, $escape=true)
	{
		if ( !is_array($data) ) {
			$data = (array)$data;
		}
		return array_merge(array('success'=>$result, 'escape'=>$escape), $data);
	}
	
  /**
   * LIKE文に渡す文字列のワイルドカードをエスケープする
   *
   * @params object $Model
   * @params string $str
   * @params boolean $before
   * @params boolean $after
   * @return string
   */
  public function escapeLike(&$Model, $str, $before = true, $after = true)
  {
    $result = str_replace('\\', '\\\\', $str); // \ -> \\
    $result = str_replace('%', '\\%', $result); // % -> \%
    $result = str_replace('_', '\\_', $result); // _ -> \_
    return (($before) ? '%' : '') . $result . (($after) ? '%' : '');
  }
}