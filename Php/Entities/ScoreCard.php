<?php

namespace Apps\Insight\Php\Entities;

use Apps\Webiny\Php\Lib\Api\ApiContainer;
use Apps\Webiny\Php\Lib\Entity\AbstractEntity;
use Apps\Webiny\Php\Entities\User;
use Apps\Webiny\Php\Lib\Entity\Indexes\IndexContainer;
use Webiny\Component\Mongo\Index\CompoundIndex;
use Webiny\Component\Mongo\Index\SingleIndex;

/**
 * Class ScoreCard
 *
 * @property string  $id
 * @property User    $user
 * @property Rule    $rule
 * @property integer $score
 * @property integer $activities
 * @property integer $lastActivity
 */
class ScoreCard extends AbstractEntity
{
    protected static $classId = 'Insight.Entities.ScoreCard';
    protected static $i18nNamespace = 'Insight.Entities.ScoreCard';
    protected static $collection = 'InsightScoreCard';

    public function __construct()
    {
        parent::__construct();
        $this->attr('user')->user()->setDefaultValue($this->wAuth()->getUser());
        $this->attr('rule')->many2one()->setEntity(Rule::class);
        $this->attr('score')->integer()->setToArrayDefault();
        $this->attr('activities')->integer()->setToArrayDefault();
        $this->attr('lastActivity')->datetime()->setToArrayDefault();
    }

    protected function entityApi(ApiContainer $api)
    {
        parent::entityApi($api);

        $api->get('user/{user}', function (User $user) {
            $scoreCard = $this->find(['user' => $user->id], ['-score']);

            return $scoreCard->toArray('user.firstName,user.lastName,user.insight,rule.name,rule.description,score,activities,lastActivity');
        });
    }


    protected static function entityIndexes(IndexContainer $indexes)
    {
        parent::entityIndexes($indexes);
        $indexes->add(new SingleIndex('user', 'user'));
        $indexes->add(new SingleIndex('rule', 'rule'));
        $indexes->add(new CompoundIndex('user-rule', ['user', 'rule'], false, true));
    }


    /**
     * Returns level based on the give score.
     *
     * @param integer $score Score
     *
     * @return int
     */
    public function getLevel($score)
    {
        $level = 0;

        do {
            $level++;
        } while ((pow(2, $level)) < $score);

        return $level;
    }
}