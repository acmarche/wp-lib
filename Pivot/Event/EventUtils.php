<?php


namespace AcMarche\Pivot\Event;


use AcMarche\Pivot\Entities\OffreInterface;
use DateTime;

class EventUtils
{
    private static $today = null;

    public static function isEventObsolete(OffreInterface $event)
    {
        self::$today = new DateTime();
        $horlines    = [];
        foreach ($event->dates() as $horline) {
            if ( ! self::isObsolete($horline->year, $horline->month, $horline->day)) {
                $horlines[] = $horline;
            }
        }
        if (count($horlines) == 0) {
            return true;
        }

        return false;
    }

    public static function sortDates(OffreInterface $event): void
    {
        $dates = $event->dates();
        usort(
            $dates,
            function ($a, $b) {
                {
                    $debut1 = $a->year.'-'.$a->month.'-'.$a->day;
                    $debut2 = $b->year.'-'.$b->month.'-'.$b->day;
                    if ($debut1 == $debut2) {
                        return 0;
                    }

                    return ($debut1 < $debut2) ? 1 : -1;
                }
            }
        );
    }

    /**
     * @param array $events
     *
     * @return OffreInterface[]
     */
    public static function sortEvents(array $events): array
    {
        usort(
            $events,
            function ($eventA, $eventB) {
                {
                    $horlineA = $eventA->firstHorline();
                    $horlineB = $eventB->firstHorline();

                    $debut1 = $horlineA->year.'-'.$horlineA->month.'-'.$horlineA->day;
                    $debut2 = $horlineB->year.'-'.$horlineB->month.'-'.$horlineB->day;
                    if ($debut1 == $debut2) {
                        return 0;
                    }

                    return ($debut1 < $debut2) ? -1 : 1;
                }
            }
        );

        return $events;
    }

    private static function isObsolete(string $year, string $month, string $day): bool
    {
        $dateEnd = $year.'-'.$month.'-'.$day;
        if ($dateEnd < self::$today->format('Y-m-d')) {
            return true;
        }

        return false;
    }
}
