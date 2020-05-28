<?php

declare(strict_types=1);

namespace App\Model\Core\Service\AnswerMangerService;

use App\Model\Core\Service\AnswerMangerService\Models\IAnswer;
use App\Model\Core\Service\AnswerMangerService\Models\IRepeat;
use App\Model\Core\Service\AnswerMangerService\Models\ISettings;
use App\Model\Core\Service\DateIntervalConverter;
use DateInterval;
use DateTimeImmutable;

class AnswerManagerService
{
    private $converter;

    public function __construct(DateIntervalConverter $converter)
    {
        $this->converter = $converter;
    }

    public function getRepeatInterval(IRepeat $repeat, ISettings $settings, IAnswer $answer): DateInterval
    {
        if ($repeat->isNew()) {
            return $settings->getBaseInterval();
        } elseif ($repeat->isStudied()) {
            $previewRepeatInterval = $this->converter->toSeconds($repeat->getRepeatInterval());
            $averageTimeIndex = $this->getAverageIndex(
                $repeat->getTotalTime(),
                $repeat->getCount(),
                $answer->getTime()
            );
            $simpleIndex = $previewRepeatInterval * $answer->getEstimateAnswer();
            $complexIndex = $simpleIndex  * $averageTimeIndex;
            return  $this->makeInterval($complexIndex, $settings);
        } else {
            $previewRepeatInterval = $this->converter->toSeconds($repeat->getRepeatInterval());
            $simpleIndex = $previewRepeatInterval * $answer->getEstimateAnswer();
            $countRepeatIndex = $repeat->getSuccessCount() / $repeat->getCount();
            $averageTimeIndex = $this->getAverageIndex(
                $repeat->getTotalTime(),
                $repeat->getCount(),
                $answer->getTime()
            );
            $complexIndex = $simpleIndex * $countRepeatIndex / $averageTimeIndex;
            return  $this->makeInterval($complexIndex, $settings);
        }
    }

    private function getAverageIndex(DateInterval $totalTime, int $countRepeat, DateInterval $currentTime)
    {
        $totalTimeSec = $this->converter->toSeconds($totalTime);
        $currentTimeSec = $this->converter->toSeconds($currentTime);
        $averageTimeSec = $totalTimeSec / $countRepeat;
        return $currentTimeSec / $averageTimeSec;
    }

    private function makeInterval(int $complexIndex, ISettings $settings): DateInterval
    {
        $minTimeSec = $this->converter->toSeconds($settings->getMinTimeRepeat());
        if ($complexIndex < $minTimeSec) {
            $complexIndex = $minTimeSec;
        }
        return DateInterval::createFromDateString(((int)$complexIndex).' seconds');
    }
}
