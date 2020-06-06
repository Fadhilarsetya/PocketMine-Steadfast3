<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

/**
 * Various Utilities used around the code
 */
namespace pocketmine\utils;

use InvalidArgumentException;
use function chr;
use function define;
use function defined;
use function ord;
use function pack;
use function preg_replace;
use function round;
use function sprintf;
use function substr;
use function unpack;
use const PHP_INT_MAX;
use pocketmine\entity\Entity;
use pocketmine\utils\MetadataConvertor;

if(!defined("ENDIANNESS")){
	define("ENDIANNESS", (pack("s", 1) === "\0\1" ? Binary::BIG_ENDIAN : Binary::LITTLE_ENDIAN));
}

class Binary{
	const BIG_ENDIAN = 0x00;
	const LITTLE_ENDIAN = 0x01;

	public static function signByte($value) {
		if(PHP_INT_SIZE === 8) {
			return $value << 56 >> 56;
		} else {
			return $value << 24 >> 24;
		}
	}

	public static function unsignByte($value) {
		return $value & 0xff;
	}

	public static function signShort($value) {
		if(PHP_INT_SIZE === 8) {
			return $value << 48 >> 48;
		} else {
			return $value << 16 >> 16;
		}	
	}

	public static function unsignShort($value) {
		return $value & 0xffff;
	}

	public static function signInt($value) {
		if(PHP_INT_SIZE === 8) {
			return $value << 32 >> 32;
		} else {
			return $value;
		}
	}

	public static function unsignInt($value) {
		return $value & 0xffffffff;
	}


	public static function flipShortEndianness($value) {
		return self::readLShort(self::writeShort($value));
	}

	public static function flipIntEndianness($value) {
		return self::readLInt(self::writeInt($value));
	}

	public static function flipLongEndianness($value) {
		return self::readLLong(self::writeLong($value));
	}

	/**
	 * Reads a byte boolean
	 *
	 * @param string $b
	 *
	 * @return bool
	 */
	public static function readBool($b) {
		return $b !== "\x00";
	}

	/**
	 * Writes a byte boolean
	 *
	 * @param bool $b
	 *
	 * @return string
	 */
	public static function writeBool($b) {
		return $b ? "\x01" : "\x00";
	}

	/**
	 * Reads an unsigned byte (0 - 255)
	 *
	 * @param string $c
	 *
	 * @return int
	 */
	public static function readByte($c, $signed = true) {
		if($signed == true) {
			return self::signByte(ord($c[0]));
		} else {
			return ord($c[0]);
		}
	}

	/**
	 * Writes an unsigned/signed byte
	 *
	 * @param int $c
	 *
	 * @return string
	 */
	public static function writeByte($c) {
		return chr($c);
	}

	/**
	 * Reads a 16-bit unsigned big-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readShort($str) {
		return unpack("n", $str)[1];
	}

	/**
	 * Reads a 16-bit signed big-endian number
	 *
	 * @param $str
	 *
	 * @return int
	 */
	public static function readSignedShort($str) {
		return self::signShort(unpack("n", $str)[1]);
	}

	/**
	 * Writes a 16-bit signed/unsigned big-endian number
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeShort($value) {
		return pack("n", $value);
	}

	/**
	 * Reads a 16-bit unsigned little-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readLShort($str) {
		return unpack("v", $str)[1];
	}

	/**
	 * Reads a 16-bit signed little-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readSignedLShort($str) {
		return self::signShort(unpack("v", $str)[1]);
	}

	/**
	 * Writes a 16-bit signed/unsigned little-endian number
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeLShort($value) {
		return pack("v", $value);
	}

	/**
	 * Reads a 3-byte big-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readTriad($str) {
		return unpack("N", "\x00" . $str)[1];
	}

	/**
	 * Writes a 3-byte big-endian number
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeTriad($value) {
		return substr(pack("N", $value), 1);
	}

	/**
	 * Reads a 3-byte little-endian number
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readLTriad($str) {
		return unpack("V", $str . "\x00")[1];
	}

	/**
	 * Writes a 3-byte little-endian number
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeLTriad($value) {
		return substr(pack("V", $value), 0, -1);
	}

	/**
	 * Reads a 4-byte signed integer
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readInt($str) {
		return self::signInt(unpack("N", $str)[1]);
	}

	/**
	 * Writes a 4-byte integer
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeInt($value) {
		return pack("N", $value);
	}

	/**
	 * Reads a 4-byte signed little-endian integer
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readLInt($str) {
		return self::signInt(unpack("V", $str)[1]);
	}

	/**
	 * Writes a 4-byte signed little-endian integer
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeLInt($value) {
		return pack("V", $value);
	}

	/**
	 * Reads a 4-byte floating-point number
	 *
	 * @param string $str
	 *
	 * @return float
	 */
	public static function readFloat($str) {
		return ENDIANNESS === self::BIG_ENDIAN ? unpack("f", $str)[1] : unpack("f", strrev($str))[1];
	}

	/**
	 * Reads a 4-byte floating-point number, rounded to the specified number of decimal places.
	 *
	 * @param string $str
	 * @param int    $accuracy
	 *
	 * @return float
	 */
	public static function readRoundedFloat($str, $accuracy) {
		return round(self::readFloat($str), $accuracy);
	}

	/**
	 * Writes a 4-byte floating-point number.
	 *
	 * @param float $value
	 *
	 * @return string
	 */
	public static function writeFloat($value) {
		return ENDIANNESS === self::BIG_ENDIAN ? pack("f", $value) : strrev(pack("f", $value));
	}

	/**
	 * Reads a 4-byte little-endian floating-point number.
	 *
	 * @param string $str
	 *
	 * @return float
	 */
	public static function readLFloat($str) {
		return ENDIANNESS === self::BIG_ENDIAN ? unpack("f", strrev($str))[1] : unpack("f", $str)[1];
	}

	/**
	 * Reads a 4-byte little-endian floating-point number rounded to the specified number of decimal places.
	 *
	 * @param string $str
	 * @param int    $accuracy
	 *
	 * @return float
	 */
	public static function readRoundedLFloat($str, $accuracy) {
		return round(self::readLFloat($str), $accuracy);
	}

	/**
	 * Writes a 4-byte little-endian floating-point number.
	 *
	 * @param float $value
	 *
	 * @return string
	 */
	public static function writeLFloat($value) {
		return ENDIANNESS === self::BIG_ENDIAN ? strrev(pack("f", $value)) : pack("f", $value);
	}

	/**
	 * Returns a printable floating-point number.
	 *
	 * @param float $value
	 *
	 * @return string
	 */
	public static function printFloat($value) {
		return preg_replace("/(\\.\\d+?)0+$/", "$1", sprintf("%F", $value));
	}

	/**
	 * Reads an 8-byte floating-point number.
	 *
	 * @param string $str
	 *
	 * @return float
	 */
	public static function readDouble($str) {
		return ENDIANNESS === self::BIG_ENDIAN ? unpack("d", $str)[1] : unpack("d", strrev($str))[1];
	}

	/**
	 * Writes an 8-byte floating-point number.
	 *
	 * @param float $value
	 *
	 * @return string
	 */
	public static function writeDouble($value) {
		return ENDIANNESS === self::BIG_ENDIAN ? pack("d", $value) : strrev(pack("d", $value));
	}

	/**
	 * Reads an 8-byte little-endian floating-point number.
	 *
	 * @param string $str
	 *
	 * @return float
	 */
	public static function readLDouble($str) {
		return ENDIANNESS === self::BIG_ENDIAN ? unpack("d", strrev($str))[1] : unpack("d", $str)[1];
	}

	/**
	 * Writes an 8-byte floating-point little-endian number.
	 *
	 * @param float $value
	 *
	 * @return string
	 */
	public static function writeLDouble($value) {
		return ENDIANNESS === self::BIG_ENDIAN ? strrev(pack("d", $value)) : pack("d", $value);
	}

	/**
	 * Reads an 8-byte integer.
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readLong($x){
		if(PHP_INT_SIZE === 8){
			$int = unpack("N*", $x);
			return ($int[1] << 32) | $int[2];
		}else{
			$value = "0";
			for($i = 0; $i < 8; $i += 2){
				$value = bcmul($value, "65536", 0);
				$value = bcadd($value, self::readShort(substr($x, $i, 2)), 0);
			}

			if(bccomp($value, "9223372036854775807") == 1){
				$value = bcadd($value, "-18446744073709551616");
			}

			return $value;
		}
	}

	/**
	 * Writes an 8-byte integer.
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeLong($value) {
		if(PHP_INT_SIZE === 8){
			return pack("NN", $value >> 32, $value & 0xFFFFFFFF);
		}else{
			$x = "";
			$value = (string) $value;

			if(bccomp($value, "0") == -1){
				$value = bcadd($value, "18446744073709551616");
			}

			$x .= self::writeShort((int) bcmod(bcdiv($value, "281474976710656"), "65536"));
			$x .= self::writeShort((int) bcmod(bcdiv($value, "4294967296"), "65536"));
			$x .= self::writeShort((int) bcmod(bcdiv($value, "65536"), "65536"));
			$x .= self::writeShort((int) bcmod($value, "65536"));

			return $x;
		}
	}

	/**
	 * Reads an 8-byte little-endian integer.
	 *
	 * @param string $str
	 *
	 * @return int
	 */
	public static function readLLong($str){
		return self::readLong(strrev($str));
	}

	/**
	 * Writes an 8-byte little-endian integer.
	 *
	 * @param int $value
	 *
	 * @return string
	 */
	public static function writeLLong($value){
		return strrev(self::writeLong($value));
	}

	/**
	 * Reads a 32-bit zigzag-encoded variable-length integer.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int
	 */
	public static function readSignedVarInt($stream) {
		$shift = PHP_INT_SIZE === 8 ? 63 : 31;
		$raw = self::readVarInt($stream);
		$temp = ((($raw << $shift) >> $shift) ^ $raw) >> 1;
		return $temp ^ ($raw & (1 << $shift));
	}

	/**
	 * Reads a 32-bit variable-length unsigned integer.
	 *
	 * @param string $buffer
	 * @param int    &$offset
	 *
	 * @return int
	 *
	 * @throws BinaryDataException if the var-int did not end after 5 bytes or there were not enough bytes
	 */
	public static function readVarInt($stream) {
		$value = 0;
		$i = 0;
		do{
			if($i > 63){
				throw new \InvalidArgumentException("Varint did not terminate after 10 bytes!");
			}
			$value |= ((($b = $stream->getByte()) & 0x7f) << $i);
			$i += 7;
		}while($b & 0x80);

		return $value;
	}

	/**
	 * Writes a 32-bit integer as a zigzag-encoded variable-length integer.
	 *
	 * @param int $v
	 *
	 * @return string
	 */
	public static function writeSignedVarInt($v) {
		return self::writeVarInt(($v << 1) ^ ($v >> (PHP_INT_SIZE === 8 ? 63 : 31)));
	}

	/**
	 * Writes a 32-bit unsigned integer as a variable-length integer.
	 *
	 * @param int $value
	 *
	 * @return string up to 5 bytes
	 */
	public static function writeVarInt($value) {
		$buf = "";
		for($i = 0; $i < 10; ++$i){
			if(($value >> 7) !== 0){
				$buf .= chr($value | 0x80); //Let chr() take the last byte of this, it's faster than adding another & 0x7f.
			}else{
				$buf .= chr($value & 0x7f);
				return $buf;
			}

			$value = (($value >> 7) & (PHP_INT_MAX >> 6)); //PHP really needs a logical right-shift operator
		}

		throw new \InvalidArgumentException("Value too large to be encoded as a varint");
	}
	
	/**
	 * Writes a coded metadata string
	 *
	 * @param array $data
	 *
	 * @return string
	 */
	public static function writeMetadata(array $data, $playerProtocol){
		$data = MetadataConvertor::updateMeta($data, $playerProtocol);
        $m = "";
        $m .= self::writeVarInt(count($data));
        foreach($data as $bottom => $d){
			switch($d[0]){
				case Entity::DATA_TYPE_UNSIGNED_LONG:
                    $type = Entity::DATA_TYPE_LONG;
                    break;
                default:
                    $type = $d[0];
                    break;
			}
			$m .= self::writeVarInt($bottom);
            $m .= self::writeVarInt($type);
			switch($d[0]){
                case Entity::DATA_TYPE_BYTE:
                    $m .= self::writeByte($d[1]);
                    break;
                case Entity::DATA_TYPE_SHORT:
                    $m .= self::writeLShort($d[1]);
                    break;
                case Entity::DATA_TYPE_LONG:
                case Entity::DATA_TYPE_INT:
                    $m .= self::writeSignedVarInt($d[1]);
                    break;
                case Entity::DATA_TYPE_FLOAT:
                    $m .= self::writeLFloat($d[1]);
                    break;
                case Entity::DATA_TYPE_STRING:
                    $m .= self::writeVarInt(strlen($d[1])) . $d[1];
                    break;
                case Entity::DATA_TYPE_SLOT:
                    $m .= "\x7f";
                    break;
                case Entity::DATA_TYPE_POS:
                    $m .= self::writeSignedVarInt($d[1][0]);
                    $m .= self::writeSignedVarInt($d[1][1]);
                    $m .= self::writeSignedVarInt($d[1][2]);
                    break;
                case Entity::DATA_TYPE_UNSIGNED_LONG:
                    $m .= self::writeVarInt($d[1]);
                    break;
                case Entity::DATA_TYPE_VECTOR3:
                    $m .= self::writeLFloat($d[1][0]);
                    $m .= self::writeLFloat($d[1][1]);
                    $m .= self::writeLFloat($d[1][2]);
                    break;
            }
		}

		return $m;
	}

}
