
<?php
namespace App\Es;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

final class ClientFactory7 {
    public static function make(array $hosts, ?string $user, ?string $pass, bool $verify): Client {
        $builder = ClientBuilder::create()->setHosts($hosts);
        if ($user && $pass) {
            $builder->setBasicAuthentication($user, $pass);
        }
        return $builder->build();
    }
}
