<?php


namespace App\Services\Chart;

use CMEN\GoogleChartsBundle\GoogleCharts\Options\VAxis;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\ComboChart;
use CMEN\GoogleChartsBundle\GoogleCharts\Options\ComboChart\Series;

class Chart
{
    const ANIMATION_STARTUP = true;
    const ANIMATION_DURATION = 1000;
    const CHART_AREA_HEIGHT = '80%';
    const CHART_AREA_WIDTH = '80%';

    private $chartData;


    public function __construct(ChartData $chartData)
    {
        $this->chartData = $chartData;
    }


    /**
     * Crée le graphique du montant des bénéfices par année.
     *
     * @return ComboChart
     */
    public function amountByYear()
    {
        $arrayToDataTable = $this->chartData->dataAmountByYear();

        $chart = new ComboChart();
        $chart->getData()->setArrayToDataTable($arrayToDataTable);
        $chart->getOptions()->getAnimation()->setStartup(self::ANIMATION_STARTUP);
        $chart->getOptions()->getAnimation()->setDuration(self::ANIMATION_DURATION);
        $chart->getOptions()->getChartArea()->setHeight(self::CHART_AREA_HEIGHT);
        $chart->getOptions()->getChartArea()->setWidth(self::CHART_AREA_WIDTH);

        $vAxisAmount = new VAxis();
        $vAxisAmount->setTitle('Montant en €');
        $vAxisEvol = new VAxis();
        $vAxisEvol->setTitle('Evolution en %');
        $chart->getOptions()->setVAxes([$vAxisAmount, $vAxisEvol]);

        $seriesAmount = new Series();
        $seriesAmount->setType('bars');
        $seriesAmount->setTargetAxisIndex(0);
        $seriesEvol = new Series();
        $seriesEvol->setType('line');
        $seriesEvol->setTargetAxisIndex(1);
        $chart->getOptions()->setSeries([$seriesAmount, $seriesEvol]);

        $chart->getOptions()->getHAxis()->setTitle('Année');
        $chart->getOptions()->setColors(['#f6dc12', '#759e1a']);

        return $chart;
    }
}