<?php
namespace App\Globals;



class Visitor
{
	public static function use_the_counter()
	{
		$counter = 0;
		$handle  = fopen("visitor_counter.txt", "r");
		if($handle)
		{
			$counter = (int ) fread($handle,20000);
			fclose($handle);
			$counter++;
			$handle = fopen("visitor_counter.txt", "w");
			fwrite($handle,$counter) ;
			fclose($handle) ;	
		}
	}

	public static function get_all_visitors()
	{
		$counter = 0;
		$handle = fopen("visitor_counter.txt", "r");
		if($handle)
		{
			$counter = (int ) fread($handle,20000);
		}

		$return['all'] 	= $counter;
		$return['week'] = round($counter*0.2);
		return $return;
	}
}
