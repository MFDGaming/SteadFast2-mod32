<?php

/*
                                 Copyright MFDGaming
                   This file is licensed under the LGPLv3 license
            if you do not own a copy of this licence you can get one from
                    https://www.gnu.org/licenses/lgpl-3.0.en.html
 */

namespace pocketmine\utils;

class BCBitwise {
	public static function leftShift(string $x, string $y) : string {
		bcscale(0);
		return bcmul((string) $x, bcpow("2", (string) $y));
	}

	public static function rightShift(string $x, string $y) : string {
		bcscale(0);
		return bcdiv((string) $x, bcpow("2", (string) $y));
	}

	private static function getDigits() : string {
		return hex2bin("000102030405060708090a0b0c0d0e0f101112131415161718191a1b1c1d1e1f202122232425262728292a2b2c2d2e2f303132333435363738393a3b3c3d3e3f404142434445464748494a4b4c4d4e4f505152535455565758595a5b5c5d5e5f606162636465666768696a6b6c6d6e6f707172737475767778797a7b7c7d7e7f808182838485868788898a8b8c8d8e8f909192939495969798999a9b9c9d9e9fa0a1a2a3a4a5a6a7a8a9aaabacadaeafb0b1b2b3b4b5b6b7b8b9babbbcbdbebfc0c1c2c3c4c5c6c7c8c9cacbcccdcecfd0d1d2d3d4d5d6d7d8d9dadbdcdddedfe0e1e2e3e4e5e6e7e8e9eaebecedeeeff0f1f2f3f4f5f6f7f8f9fafbfcfdfeff");
	}

	private static function fromDecimal(string $decimal) : string {
		bcscale(0);
		$value = "";
		$digits = self::getDigits();
		while (bccomp((string) $decimal, "255") == 1) {
			$rest = bcmod((string) $decimal, "256");
			$decimal = bcdiv((string) $decimal, "256");
			$value = $digits[intval($rest)] . $value;
		}
		$value = $digits[intval($decimal)] . $value;
		return (string) $value;
	}

	private static function toDecimal(string $value) : string {
		bcscale(0);
		$digits = self::getDigits();
		$size = strlen($value);
		$decimal = "0";
		for ($i = 0; $i < $size; ++$i) {
			$element = strpos($digits, $value[$i]);
			$power = bcpow("256", (string) ($size - $i - 1));
			$decimal = bcadd($decimal, bcmul((string) $element, $power));
		}
		return $decimal;
	}

	private static function fixedBinPad(string $number, int $length) : string {
		$pad = "";
		for($i = 0; $i < $length - strlen($number); ++$i) {
			$pad .= self::fromDecimal("0");
		}
		return $pad . (string) $number;
	}

	private static function equalBinPad(string &$x, string &$y) : void {
		$length = max(strlen((string) $x), strlen((string) $y));
		$x = self::fixedBinPad((string) $x, $length);
		$y = self::fixedBinPad((string) $y, $length);
	}

	private static function operator(string $x, string $y, string $op) : string {
		$bx = self::fromDecimal((string) $x);
		$by = self::fromDecimal((string)$y);
		self::equalBinPad($bx, $by);
		$ret = "";
		for($i = 0; $i < strlen($bx); ++$i) {
			$xd = substr($bx, $i, 1);
			$yd = substr($by, $i, 1);
			switch($op) {
				case "and":
					$ret .= $xd & $yd;
					break;
				case "or":
					$ret .= $xd | $yd;
					break;
				case "xor":
					$ret .= $xd ^ $yd;
					break;
			}
		}
		return self::toDecimal($ret);
	}

	public static function bitwiseAnd(string $x, string $y) : string {
		return self::operator($x, $y, "and");
	}

	public static function bitwiseOr(string $x, string $y) : string {
		return self::operator($x, $y, "or");
	}

	public static function bitwiseXor(string $x, string $y) : string {
		return self::operator($x, $y, "xor");
	}

	// This function is a
	// bit out of place but
	// it's ok for now
	public static function decToBin(string $v) : string {
		$ret = "";
		if (preg_match("/^\d+$/", (string) $v)) {
			while ($v != "0") {
				$ret .= chr(48 + ($v[strlen($v) - 1] % 2));
				$v = bcdiv($v, "2");
			}
			$ret = strrev($ret);
		}
		return (($ret != "") ? $ret : "0");
	}
}
