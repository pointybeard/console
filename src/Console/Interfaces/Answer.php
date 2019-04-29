<?php declare(strict_types=1);

namespace Kickstash\Lib\Models;

use Kickstash\Lib;
use Symphony\ClassMapper\Lib as ClassMapper;
use SymphonyPDO;

final class Answer extends Lib\AbstractKickstashModel
{
    use ClassMapper\Traits\hasClassMapperTrait;
    use Lib\Traits\hasUuidTrait;
    use Lib\Traits\hasModifiedDateTrait;

    protected static function getCustomFieldMapping()
    {
        return [
            'survey' => [
                'databaseFieldName' => 'relation_id',
                'classMemberName' => 'surveyId',
                'flags' => self::FLAG_INT | self::FLAG_REQUIRED
            ],

            'question' => [
                'databaseFieldName' => 'relation_id',
                'classMemberName' => 'questionId',
                'flags' => self::FLAG_INT | self::FLAG_REQUIRED
            ],

            'created-at' => [
                'classMemberName' => 'dateCreatedAt'
            ],

            'modified-at' => [
                'classMemberName' => 'dateModifiedAt',
                'flags' => self::FLAG_NULL
            ],
        ];
    }

    public static function loadFromSurveyId($surveyId)
    {
        return self::fetch([
            ['surveyId', $surveyId, \PDO::PARAM_INT]
        ]);
    }

    public static function fetchByQuestionId($questionId)
    {
        return self::fetch([
            ['questionId', $questionId, \PDO::PARAM_INT]
        ]);
    }

    public function question()
    {
        // An answer is only ever attached to a single question
        return Question::loadFromId($this->questionId);
    }

    public function survey()
    {
        // An answer is only ever attached to a single survey
        return Survey::loadFromId($this->surveyId);
    }
}
