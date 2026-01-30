<?php

declare(strict_types=1);

namespace Platine\Test\Framework\Security\Signer;

use Platine\Config\Config;
use Platine\Dev\PlatineTestCase;
use Platine\Filesystem\Adapter\Local\File;
use Platine\Filesystem\Filesystem;
use Platine\Framework\Security\JWT\Exception\InvalidAlgorithmException;
use Platine\Framework\Security\JWT\Signer\OpenSSL;

/*
 * @group core
 * @group framework
 */
class OpenSSLTest extends PlatineTestCase
{
    public function testConstructInvalidAlgo(): void
    {
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.sign.signature_algo', '', 'foo_algo']
            ]
        ]);
        $filesystem = $this->getMockInstance(Filesystem::class);
        $this->expectException(InvalidAlgorithmException::class);
        $o = new OpenSSL($config, $filesystem);
    }

    public function testConstructSuccess(): void
    {
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.sign.signature_algo', '', 'sha256'],
                ['api.sign.token_header_algo', '', 'RS256'],
            ]
        ]);
        $filesystem = $this->getMockInstance(Filesystem::class);
        $o = new OpenSSL($config, $filesystem);
        $this->assertEquals('sha256', $o->getSignatureAlgo());
        $this->assertEquals('RS256', $o->getTokenAlgoName());
    }

    public function testSignPrivateKeyFileNotExist(): void
    {
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.sign.signature_algo', '', 'sha256'],
            ]
        ]);
        $file = $this->getMockInstance(File::class, [
            'exists' => false
        ]);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'file' => $file,
        ]);
        $o = new OpenSSL($config, $filesystem);
        $this->expectException(InvalidAlgorithmException::class);
        $o->sign('foo', 'private_key_file');
    }

    public function testSignFailed(): void
    {
        global $mock_openssl_sign_to_false;
        $mock_openssl_sign_to_false = true;
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.sign.signature_algo', '', 'sha256'],
            ]
        ]);
        $file = $this->getMockInstance(File::class, [
            'exists' => true,
            'read' => $this->getPrivateKeyContent(),
        ]);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'file' => $file,
        ]);
        $o = new OpenSSL($config, $filesystem);
        $this->expectException(InvalidAlgorithmException::class);
        $o->sign('my_data', 'private_key_file');
    }

    public function testSign(): void
    {
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.sign.signature_algo', '', 'sha256'],
            ]
        ]);
        $file = $this->getMockInstance(File::class, [
            'exists' => true,
            'read' => $this->getPrivateKeyContent(),
        ]);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'file' => $file,
        ]);
        $o = new OpenSSL($config, $filesystem);
        $res = $o->sign('my_data', 'private_key_file');
        $expected = 'buesc83NNlTbtHzLY2UD8kwlNo9kMCl8DgqRl5gOYR/7b2rQ77Gw4Vuoy1'
                . 'sF/Cr3eQ+LOxy5U+lmdvRMU80vPN+ue1futsQ0hGPniLorIB+XsIs0P4joSe'
                . 'nO+nXje5d84W57VuriLNlCvz2GJ6DH3t9japUi15Yin0BQ70JPH3ULTqeFuY'
                . '5GWnmQXFD9//H5Yid6221qv/ZImFrrCI/FlJeZayLHG1n1KxZsmqeDegfYI'
                . '9dUYSxCIjsp9phfpHf+mjrja0Ld+GOyn/S+rS0rAIyt2mAUe4Nj6PqwmrqA'
                . 'p0NfzRWtWIWSPhwJXJ5VpY+83C8h5me5A1kpnsUD36inQA==';

        $this->assertEquals($expected, base64_encode($res));
    }

    public function testVerifyPublicKeyFileNotExist(): void
    {
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.sign.signature_algo', '', 'sha256'],
                ['api.sign.public_key', '', 'sha256'],
            ]
        ]);
        $file = $this->getMockInstance(File::class, [
            'exists' => false
        ]);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'file' => $file,
        ]);
        $o = new OpenSSL($config, $filesystem);
        $this->expectException(InvalidAlgorithmException::class);
        $o->verify('my_data', 'signature', 'my_key');
    }

    public function testVerify(): void
    {
        $config = $this->getMockInstanceMap(Config::class, [
            'get' => [
                ['api.sign.signature_algo', '', 'sha256'],
                ['api.sign.public_key', '', 'sha256'],
            ]
        ]);
        $file = $this->getMockInstance(File::class, [
            'exists' => true,
            'read' => $this->getPublicKeyContent(),
        ]);
        $filesystem = $this->getMockInstance(Filesystem::class, [
            'file' => $file,
        ]);
        $o = new OpenSSL($config, $filesystem);
        $signature = base64_decode('buesc83NNlTbtHzLY2UD8kwlNo9kMCl8DgqRl5gOYR/7b2rQ77Gw4Vuoy1'
                . 'sF/Cr3eQ+LOxy5U+lmdvRMU80vPN+ue1futsQ0hGPniLorIB+XsIs0P4joSe'
                . 'nO+nXje5d84W57VuriLNlCvz2GJ6DH3t9japUi15Yin0BQ70JPH3ULTqeFuY'
                . '5GWnmQXFD9//H5Yid6221qv/ZImFrrCI/FlJeZayLHG1n1KxZsmqeDegfYI'
                . '9dUYSxCIjsp9phfpHf+mjrja0Ld+GOyn/S+rS0rAIyt2mAUe4Nj6PqwmrqA'
                . 'p0NfzRWtWIWSPhwJXJ5VpY+83C8h5me5A1kpnsUD36inQA==');


        $res = $o->verify('my_key', $signature, 'my_data');

        $this->assertTrue($res);
    }

    private function getPrivateKeyContent(): string
    {
        return <<<E
-----BEGIN RSA PRIVATE KEY-----
MIIEpQIBAAKCAQEAs0dzEOthUSt+a3ldNZYyg3zgku+83CLiSTLIUXX2CHn7zljb
tBSkS8K/gPIYfs4rI2y/s327XCsTeegrz2IcUCqSExwv0ybynwipYbUXgJDhst6U
0umLYSCHPE7m6KC+BrGM4Vf3WKxq/rYR8ROeya/C1WKolkeln1hKub3pFAnXUuXX
SB9WmUpJpTnCc4Fqi7TP5H7tUDvU+YP1o0YpJkWd55mrSuNQpboVtEikggz9BxQB
P7SmAbxbM2h2/oN7D+ncVqA2OSg9yUq7yteFxIpYlXD0Kjx89cp5N3BeK3uqsPxB
vJcr6NvVLpJBSO5YgGpr8QPady+VAuRv1+UAiwIDAQABAoIBAQClMDaD5n467lqD
6MXLtGNC6HN8sE1SgQoD7DjLZmeWk99C2HlHyqU/8WfHBksvvAPfljpkxns2h7Jg
Z17GrV3tN+x0k0o1wvNXOgHoN5Y6LkToLVLvq6VzjydMmF8HBeVSAZMPp6APIn9L
xrNtcGik6cAaIde48LdYxV/FGYmDnarAQVrNQxxirkmvhRfGogT8Wwsz50TEDuB/
r1yyWQNnmfh03REal4G1SQ5aUfyIr9/R/vApczqY/e5kTxe8ePDjn2vHdJvX4n3i
e+DyBhu3rtCw2yj0ZAZFULUiUrjTX4AAXx0eQ6gttronIxGKMhjPv7GtXka5z5ZY
T9B2zFvxAoGBANzteJnbKJJudYmSXw5pmSa0Oxfa9zerLK1RNiu6FoEyVpiCDZib
IBt5bGiaSN/jxBZE7V9FmUChzix8qtfrNl8RRBSNm7kb/+QSF2PbOaO9OmHFol2f
O4r5T8miPaT6nlr3bvoUbVNnkAOL4JZKaKySmAZaAJFvXwG/C6M+a+zvAoGBAM+9
YHDjpn2ZaJW5EbMdnOPk595h/L91I3wUDYjsFk44IZab+6U+bIt1U/Xi19imm/Ua
5SirWaN48Mm/VyNa00weL/Yz7vJ1TTnFm2XIQyCI+ecgnEaXBb/7LYbqB20EXc9h
yIGjMQ0CJPpm8FcbtSpdhO24hWz/RnjczbJ7dF4lAoGBAMEAgBxA9le+QdZWVc58
d/v09a7obpJmathamy1mGyTw+e+vpfsMgecek5NqPYHCM9qUip5xaempsTjyDDom
1NAGgGhIMaNsD4eKOn7U1Kzzsd4aTrblVZoaJRbsMlg/TToA8SVt1PhI/+npN+Fz
za/9POxHkjl7hw86fAs3jAdnAoGBALRtvgquntct29lWsVnJmY+SLBVJM/WyLszA
ufS3YLQ1Km8eMVWXPz1s/SxuBpzkMEaVQn2tPaCvFSuU5YEOjMDq4ytKdfneCeHo
kIy5gBwM/EhTWYgx+UuCREuOjj6QcU4CltyJububpjRaHdr6dMJEEYVLI4/EtBeb
Z4jikvKFAoGAbZB0ocAB0Z8tIV+9E9mviNNArBegzVxqF0rTUQqn6aWUrpuRBKYv
mx8xbRz7BVzm/fP87U8DR69DshGuw0t0OengpR8KkC3Bmo64FqMxnbVZp0UFAUB8
nKBHwBTfnnd4TUv8Pm+jVshJjVrvVaXpbkYM2kyNPwQYpQiiLAKAMs4=
-----END RSA PRIVATE KEY-----
E;
    }

    private function getPublicKeyContent(): string
    {
        return <<<E
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAs0dzEOthUSt+a3ldNZYy
g3zgku+83CLiSTLIUXX2CHn7zljbtBSkS8K/gPIYfs4rI2y/s327XCsTeegrz2Ic
UCqSExwv0ybynwipYbUXgJDhst6U0umLYSCHPE7m6KC+BrGM4Vf3WKxq/rYR8ROe
ya/C1WKolkeln1hKub3pFAnXUuXXSB9WmUpJpTnCc4Fqi7TP5H7tUDvU+YP1o0Yp
JkWd55mrSuNQpboVtEikggz9BxQBP7SmAbxbM2h2/oN7D+ncVqA2OSg9yUq7yteF
xIpYlXD0Kjx89cp5N3BeK3uqsPxBvJcr6NvVLpJBSO5YgGpr8QPady+VAuRv1+UA
iwIDAQAB
-----END PUBLIC KEY-----
E;
    }
}
