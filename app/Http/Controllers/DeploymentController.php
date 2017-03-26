<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DeploymentController extends Controller
{
    
    
    /**
     * @param Request $request
     */
    public function deploy(Request $request)
    {
        $commands = ['cd /alidata/www/lara/laravel-webhooks', 'git pull'];
        $signature = $request->header('X-Hub-Signature'); // $headers = getallheaders(); $headers['X-Hub-Signature']
        $payload = file_get_contents('php://input');
        if ($this->isFromGithub($payload, $signature)) {
            foreach ($commands as $command) {
                shell_exec($command);
            }
            http_response_code(200);
        } else {
            abort(403);
        }
    }
    /**
     * @param $payload
     * @param $signature
     * @return bool
     */
    private function isFromGithub($payload, $signature)
    {
        return 'sha1=' . hash_hmac('sha1', $payload, env('GITHUB_DEPLOY_TOKEN'), false) === $signature;
    }
}


/*自己总结
mkdir /alidata/www/.ssh
chmod -R 777 /alidata/www/.ssh
cd /alidata/www/.ssh
sudo -u www ssh-keygen -t rsa -C "kzrvip@gmail.com"
sudo -Hu www git clone git@github.com:forapi/laravel-webhooks.git
*/

//1. sudo mkdir /var/www/.ssh
//2. sudo chown -R www-data:www-data /var/www/.ssh/
//3. ssh-keygen -t rsa -C "your_github_email" 在home目录执行
//4. cd /var/www 然后执行 sudo -Hu www-data ssh-keygen -t rsa
//5. sudo cat /var/www/.ssh/id_rsa.pub
//6. git config --global user.name "forapi"  git config --global user.email "your_github@email"
//7. sudo -Hu www-data git clone git@...
//8. 普通 php 项目 ,可以直接用 $headers = getallheaders();
//9. $signature = $headers['X-Hub-Signature']
//10. routes 文件配置 Route::post('/deploy','DeploymentController@deploy');
//11. csrf 路由