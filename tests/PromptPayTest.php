<?php

use KS\PromptPay;

require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/PromptPay.php';

/**
 * @property KS\PromptPay PromptPay
 */
class PromptPayTest extends \PHPUnit\Framework\TestCase {
    public function testFormatTarget() {
        //Target phone number
        $target = '0899999999';
        $result = PromptPay::formatTarget($target);
        $expected = '0066899999999';
        $this->assertEquals($expected, $result);
        
        $target = '089-999-9999';
        $result = PromptPay::formatTarget($target);
        $expected = '0066899999999';
        $this->assertEquals($expected, $result);
        
        //Target ID
        $target = '1234567890123';
        $result = PromptPay::formatTarget($target);
        $expected = '1234567890123';
        $this->assertEquals($expected, $result);
        
        //Target Tax ID start with 0 (Coporate Tax ID)
        $target = '0123456789012';
        $result = PromptPay::formatTarget($target);
        $expected = '0123456789012';
        $this->assertEquals($expected, $result);
    }
    
    public function testformatAmount() {
        
        $amount = 1337.1337;
        $result = PromptPay::formatAmount($amount);
        $expected = '1337.13';
        $this->assertEquals($expected, $result);
        
        $amount = 1337.1387;
        $result = PromptPay::formatAmount($amount);
        $expected = '1337.14';
        $this->assertEquals($expected, $result);
    }
    
    public function testCrc16() {
        
        //https://www.blognone.com/node/95133
        $data = '00020101021129370016A000000677010111011300660000000005802TH53037646304';
        $result = PromptPay::crc16($data);
        $expected = '8956';
        $this->assertEquals($expected, $result);
        
        $data = '00020101021129370016A000000677010111011300668999999995802TH53037646304';
        $result = PromptPay::crc16($data);
        $expected = 'FE29';
        $this->assertEquals($expected, $result);
    }
    
    public function testGeneratePayload() {
        
        $target = '0899999999';
        $result = PromptPay::builder()->setTarget($target)->build();
        $expected = '00020101021129370016A000000677010111011300668999999995802TH53037646304FE29';
        $this->assertEquals($expected, $result);
        
        $target = '0891234567';
        $result = PromptPay::builder()->setTarget($target)->build();
        $expected = '00020101021129370016A000000677010111011300668912345675802TH5303764630429C1';
        $this->assertEquals($expected, $result);
        
        $target = '0000000000';
        $result = PromptPay::builder()->setTarget($target)->build();
        $expected = '00020101021129370016A000000677010111011300660000000005802TH530376463048956';
        $this->assertEquals($expected, $result);
        
        $target = '1234567890123';
        $result = PromptPay::builder()->setTarget($target)->build();
        $expected = '00020101021129370016A000000677010111021312345678901235802TH53037646304EC40';
        $this->assertEquals($expected, $result);
        
        //with amount
        $target = '089-123-4567';
        $amount = '13371337.75';
        $result = PromptPay::builder()->setTarget($target)->setAmount($amount)->build();
        $expected = '00020101021229370016A000000677010111011300668912345675802TH5303764541113371337.756304B7D7';
        $this->assertEquals($expected, $result);
        
        $target = '1234567890123';
        $amount = '420';
        $result = PromptPay::builder()->setTarget($target)->setAmount($amount)->build();
        $expected = '00020101021229370016A000000677010111021312345678901235802TH53037645406420.006304BF7B';
        $this->assertEquals($expected, $result);
        
        // e-Wallet ID
        // K Plus ID
        $target = '004999000288505';
        $result = PromptPay::builder()->setTarget($target)->build();
        $expected = '00020101021129390016A00000067701011103150049990002885055802TH530376463041521';
        $this->assertEquals($expected, $result);
        
        // with amount
        $target = '004999000288505';
        $amount = '100.25';
        $result = PromptPay::builder()->setTarget($target)->setAmount($amount)->build();
        $expected = '00020101021229390016A00000067701011103150049990002885055802TH53037645406100.256304369A';
        $this->assertEquals($expected, $result);
        
        // K Plus Shop ID
        $target = '004000006579718';
        $result = PromptPay::builder()->setTarget($target)->build();
        $expected = '00020101021129390016A00000067701011103150040000065797185802TH53037646304FBB5';
        $this->assertEquals($expected, $result);
        
        // with amount
        $target = '004000006579718';
        $amount = '200.50';
        $result = PromptPay::builder()->setTarget($target)->setAmount($amount)->build();
        $expected = '00020101021229390016A00000067701011103150040000065797185802TH53037645406200.5063048A37';
        $this->assertEquals($expected, $result);
    }
    
    public function testF() {
        
        $id = '00';
        $value = '01';
        $result = PromptPay::f($id, $value);
        $expected = '000201';
        $this->assertEquals($expected, $result);
        
        $id = '05';
        $value = '420';
        $result = PromptPay::f($id, $value);
        $expected = '0503420';
        $this->assertEquals($expected, $result);
    }
    
    public function testGeneratePngQrCode() {
        $savePath = '/tmp/qr.png';
        $target = '089-123-4567';
        $amount = '420';
        PromptPay::builder()->setTarget($target)->setAmount($amount)->generateQrCodeAsFile($savePath);
        $this->assertGeneratePngQrCode($savePath, $target, $amount);
        
        $target = '089-123-4567';
        $amount = NULL;
        PromptPay::builder()->setTarget($target)->setAmount($amount)->generateQrCodeAsFile($savePath);
        $this->assertGeneratePngQrCode($savePath, $target, $amount);
    }
    
    private function assertGeneratePngQrCode($savePath, $target, $amount) {
        $this->assertFileExists($savePath);
        $expected_payload = PromptPay::builder()->setTarget($target)->setAmount($amount)->build();
        
        //Use zxing.org to decode image
        $request = curl_init('https://zxing.org/w/decode');
        
        list($major, $minor) = explode('.', phpversion());
        
        // send a file
        curl_setopt($request, CURLOPT_POST, true);
        
        if ($major == 5 && $minor < 5) {
            $file_upload = '@' . $savePath;
        } else {
            $file_upload = new CurlFile($savePath, 'image/png');
        }
        
        curl_setopt($request, CURLOPT_POSTFIELDS, [
            'f' => $file_upload
        ]);
        
        curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
        $body = curl_exec($request);
        curl_close($request);
        $this->assertStringContainsString($expected_payload, $body);
        
        unlink($savePath);
    }
    
}
