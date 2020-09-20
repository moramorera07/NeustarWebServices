<?php
/**
 * Created by PhpStorm.
 * User: developer
 * Date: 19/09/20
 * Time: 05:10 PM
 */

namespace HandleDomains\Controller;
use Cake\ORM\TableRegistry;


class HandleDomainsController extends AppController
{
    /**
     * @var string Tag for success and error messages of the front-end
     */
    private $_LOGTAG = "server:api.files_controller.";

    /**
     * Add domains| api/domain/add
     *
     * @return Response 200
     *
     */

    public function add()
    {
        $data = $this->request->getData();
        error_log(print_r($data, true), 3, LOGS . 'error.log');

        try {
            $tDomains = TableRegistry::get('domains');
            $DomainsFail = [];

            $listDomains = $data['domains'];

            foreach ($listDomains as $domain) {
                $dbDomain = $tDomains->find()->where(['domain' => $domain])->first();
                if(!$dbDomain){
                    $currentDomain['domain'] = $domain;
                    $newTDomain = $tDomains->newEntity($currentDomain);
                    $tDomains->save($newTDomain);
                }else{
                    $DomainsFail[] = $domain;
                }
            }

            $DomainsSuccess = $tDomains->find('all')->toArray();

            $processResult["success"] = $DomainsSuccess;
            $processResult["fail"] = $DomainsFail;
            return $this->success($this->_LOGTAG . 'success.add', $processResult);
        } catch (Exception $e) {
            error_log($e, 3, LOGS . 'error.log');
            return $this->error($this->_LOGTAG . 'error.add');
        }
    }
}