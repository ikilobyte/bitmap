<?php
/**
 * Created by PhpStorm.
 * User: Sunny
 * Date: 2021/12/9
 * Time: 3:37 下午
 */


/**
 * Class BitMap
 */
class BitMap
{
    /**
     * @var string
     */
    protected $storagePath;

    /**
     * BitMap constructor.
     * @param string $storagePath
     */
    public function __construct(string $storagePath)
    {
        $this -> storagePath = $storagePath;
        if(!is_dir($this -> storagePath)) {
            mkdir($this -> storagePath,0755,true);
        }

    }

    /**
     * @param string $key
     * @param int $offset
     * @param int $value
     * @return int
     * 设置bit位的数据
     * (new BitMap) -> set('key',1,1);
     * (new BitMap) -> set('key',3,1);
     * (new BitMap) -> set('key',6,0);
     */
    public function set(string $key,int $offset,int $value) : int
    {
        assert(in_array($value,[0,1]));

        // 获取数据
        $binaryData = $this -> getBinaryData($key);
        $byteOffset = $this -> transformOffset2Byte($offset);

        // 这个byte上的数据，转成10进制后就是 0 ~ 255
        $byteData   = $binaryData[$byteOffset] ?? '';
        $unpack     = unpack('C*',$byteData);
        $number     = empty($unpack) ? 0 : $unpack[1];

        if($value == 1) {
            $number = $number | (1 << $offset % 8);
        } else {
            $number = $number &~ (1 << $offset % 8);
        }

        // 补齐数据，防止有跨offset，如
        // 第一次：setBit('key1',3,1);
        // 第二次：setBit('key2',560,1);
        // 这里就是补齐 3 ~ 560之间相差的数据
        while (floor($offset / 8) > strlen($binaryData)) {
            $binaryData .= pack('C',0);
        }

        $binaryData[$byteOffset] = pack('C',$number);

        return $this -> writeBinaryData($key,$binaryData);
    }


    /**
     * @param string $key
     * @return string
     * 获取完整保存数据为文件
     */
    protected function getDBFilename(string $key) : string
    {
        $filename = sprintf('%s/%s.key',$this -> storagePath,$key);
        if(!is_file($filename)) {
            touch($filename,0755);
        }

        return $filename;
    }



    /**
     * @param string $key
     * @return false|string
     * 获取数据
     */
    protected function getBinaryData(string $key)
    {
        return file_get_contents($this -> getDBFilename($key));
    }


    /**
     * @param string $key
     * @param string $data
     * @return int
     * 保存数据
     */
    protected function writeBinaryData(string $key,string $data) : int
    {
        return file_put_contents($this -> getDBFilename($key),$data);
    }

    /**
     * @param int $offset
     * @return int
     */
    protected function transformOffset2Byte(int $offset) : int
    {
        // bit位是从0开始 0 ~ 7 ，总共8个位置
        // 0 ~ 7    1字节，对应原始数据下标是0
        // 8 ~ 15   2字节，对应原始数据下标是1
        // ...

        // 向下取整
        return (int) floor($offset / 8);
    }


    /**
     * @param string $key
     * @param int $offset
     * @return int -1 数据不存在
     * 获取某一位数值
     */
    public function get(string $key, int $offset) : int
    {
        $binaryData = $this -> getBinaryData($key);

        $byteOffset = $this -> transformOffset2Byte($offset);

        $byteData   = $binaryData[$byteOffset] ?? '';

        if( empty($byteData) ) {
            return -1;
        }

        $value = unpack('C',$byteData)[1];

        return ( 1 & ($value >> ($offset % 8) ) )  >= 1  ? 1 : 0;
    }
}
