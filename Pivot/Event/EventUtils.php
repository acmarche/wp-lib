<?php


namespace AcMarche\Pivot\Event;


use AcMarche\Pivot\Event\Entity\Event;
use DateTime;

class EventUtils
{
    private static $today = null;

    public static function isEventObsolete(Event $event)
    {
        self::$today = new DateTime();
        $horlines    = [];
        foreach ($event->horaires as $horaire) {
            foreach ($horaire->horlines as $horline) {
                if ( ! self::isObsolete($horline->year, $horline->month, $horline->day)) {
                    $horlines[] = $horline;
                }
            }
        }
        if (count($horlines) == 0) {
            return true;
        }

        return false;
    }

    public static function sortDates(Event $event): void
    {
        foreach ($event->horaires as $horaire) {

            usort(
                $horaire->horlines,
                function ($a, $b) {
                    {
                        $debut1 = $a->year.'-'.$a->month.'-'.$a->day;
                        $debut2 = $b->year.'-'.$b->month.'-'.$b->day;
                        if ($debut1 == $debut2) {
                            return 0;
                        }

                        return ($debut1 < $debut2) ? -1 : 1;
                    }
                }
            );

            // $horaire->horlines = $horlines;
        }

    }

    /**
     * @param array $events
     *
     * @return Event[]
     */
    public static function sortEvents(array $events): array
    {
        usort(
            $events,
            function ($eventA, $eventB) {
                {
                    $horlineA = $eventA->firstHorline();
                    $horlineB = $eventB->firstHorline();
                    if ($horlineA == null) {
                        dump($horlineA, $eventA);
                    }

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
