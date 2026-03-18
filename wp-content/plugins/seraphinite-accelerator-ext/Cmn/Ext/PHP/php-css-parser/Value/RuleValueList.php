<?php

namespace seraph_accel\Sabberworm\CSS\Value;

class RuleValueList extends ValueList {
	public function __construct($aComponents = array(), $sSeparator = ',', $iPos = 0) {
		parent::__construct($aComponents, $sSeparator, $iPos);
	}
}