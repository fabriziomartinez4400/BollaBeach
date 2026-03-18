<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get percentage change in plain number
 *
 * @param int $oldNumber
 * @param int $newNumber
 *
 * @return int
 *
 */
if( !function_exists( 'wcusage_getPercentageChange' ) ) {
  function wcusage_getPercentageChange($oldNumber, $newNumber) {
      $decreaseValue = $oldNumber - $newNumber;
  		if($oldNumber != 0 && $decreaseValue != 0) {
  			$thechange = ($decreaseValue / $oldNumber) * 100;
  		} elseif($oldNumber == 0 && $decreaseValue == 0) {
  			$thechange = 0;
      } elseif($oldNumber == 0 && $newNumber > 0) {
  			$thechange = -100;
      } elseif(($oldNumber > 0 && $newNumber > 0) && $oldNumber == $newNumber) {
  			$thechange = 0;
  		} else {
  			$thechange = 100;
  		}
      return round($thechange, 1);
  }
}

/**
 * Check if increase of decrease in stats and show icon
 *
 * @param int $amount
 * @param int $previous
 *
 * @return string
 *
 */
if( !function_exists( 'wcusage_show_pos_neg' ) ) {
  function wcusage_show_pos_neg($amount, $previous, $showclass, $roundamount) {
    $class = "";
    $style = "";
  	if($amount != "") {
  		if($amount > 0) {
        if($showclass) { $class = "wcusage-num-pos"; }
        if(!$showclass) { $style = "color: green;"; }
  			return ' <span class="'.$class.'" style="'.$style.'"><i class="fas fa-arrow-up"></i> '.round($amount, $roundamount).'%</span>';
  		} if(is_nan($amount) || is_infinite($amount)) {
        if($showclass) { $class = "wcusage-num-pos"; }
        if(!$showclass) { $style = "color: green;"; }
  			return ' <span class="'.$class.'" style="'.$style.'"><i class="fas fa-arrow-up"></i> 0%</span>';
  		} else {
        if($showclass) { $class = "wcusage-num-neg"; }
        if(!$showclass) { $style = "color: red;"; }
  			return ' <span class="'.$class.'" style="'.$style.'"><i class="fas fa-arrow-down"></i> '.round(abs($amount), $roundamount).'%</span>';
  		}
  	}
  }
}

/**
 * Get percentage changed from old and new stats as span with icons etc.
 *
 * @param int $newNumber
 * @param int $oldNumber
 * @param bool $showcurrency
 *
 * @return string
 *
 */
 if( !function_exists( 'wcusage_getPercentageChange2' ) ) {
  function wcusage_getPercentageChange2($newNumber, $oldNumber, $showcurrency) {

  	if($showcurrency) {
  		$currencysymbol = wcusage_get_currency_symbol();
  	} else {
  		$currencysymbol = "";
  	}

  	$decreaseValue = $oldNumber - $newNumber;

  	if($oldNumber != 0) {

  		if($newNumber >= $oldNumber) {

  			$thechange = abs(($decreaseValue / $oldNumber) * 100);

  		} else {

  			$thechange = "-" . ($decreaseValue / $oldNumber) * 100;

  		}

  	} elseif($oldNumber == 0 && $newNumber > 0) {

  		$thechange = 100;

  	} else {

  		$thechange = 0;

  	}

  	if($thechange >= 0) {
  		return ' <span class="wcusage-num-pos" style="color: green;" title="' . esc_html__( 'Previous', 'woo-coupon-usage' ) . ": " . $currencysymbol .  round($oldNumber, 2) . '"><i class="fas fa-arrow-up"></i> '.round($thechange, 1).'%</span>';
  	} else {
  		return ' <span class="wcusage-num-neg" style="color: red;" title="' . esc_html__( 'Previous', 'woo-coupon-usage' ) . ": " . $currencysymbol . round($oldNumber, 2) . '"><i class="fas fa-arrow-down"></i> '.round($thechange, 1).'%</span>';
  	}

  }
}

/**
 * Get percentage changed from old and new stats as raw number
 *
 * @param int $newNumber
 * @param int $oldNumber
 *
 * @return int
 *
 */
if( !function_exists( 'wcusage_getPercentageChangeNum' ) ) {
  function wcusage_getPercentageChangeNum($newNumber, $oldNumber) {

  	$decreaseValue = $oldNumber - $newNumber;

  	if($oldNumber != 0) {

  		if($newNumber >= $oldNumber) {

  			$thechange = abs(($decreaseValue / $oldNumber) * 100);

  		} else {

  			$thechange = "-" . ($decreaseValue / $oldNumber) * 100;

  		}

  	} elseif($oldNumber == 0 && $newNumber > 0) {

  		$thechange = 100;

  	} else {

  		$thechange = 0;

  	}

  	return $thechange;

  }
}
