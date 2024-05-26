<?php

declare(strict_types=1);

/*
 * 本文件由 秋枫雁飞 编写，所有解释权归Aiweline所有。
 * 邮箱：aiweline@qq.com
 * 网址：aiweline.com
 * 论坛：https://bbs.aiweline.com
 */

namespace Weline\Admin\Block\Backend;

use Weline\Framework\App\Exception;

class Total extends \Weline\Framework\View\Block
{
    /**
     * @throws Exception
     */
    public function __init()
    {
        parent::__init();
        # 如果没有模板，则使用默认模板
        if (!$this->getTemplate()) {
            $this->setTemplate('Weline_Admin::backend/total-cart.phtml');
        }
        # 检测模型是否提供
        if (!$this->getData('model')) {
            throw new Exception('未提供模型model参数');
        }
        # 检测标题是否提供
        if (!$this->getData('title')) {
            throw new Exception('未提供标题title参数');
        }
        # 检测提示是否提供
        if (!$this->getData('tip')) {
            throw new Exception('未提供提示tip参数');
        }
        # 检测比较期限是否提供
        if (!$this->getData('last_period')) {
            # 默认用昨日期限
            $this->setData('last_period', 'yesterday');
        }
        if (!$this->getData('now_period')) {
            # 默认用今日期限
            $this->setData('now_period', 'today');
        }
        $model = $this->_getModel($this->getData('model'));
        $total = $model->total();
        $this->assign('total', $total);
        # period上期数据总计
        $last_period_total = $model->period($this->getData('last_period'))->total();
        $this->assign('last_period_total', $last_period_total);
        # period本期数据总计
        $now_period_total = $model->period($this->getData('now_period'))->total();
        $this->assign('now_period_total', $now_period_total);
        # 相比较上涨率 除数不能为0
        if ($last_period_total == 0) {
            $rate = $now_period_total * 100;
        } else {
            $rate = (($now_period_total - $last_period_total) / $last_period_total) * 100;
        }
        $rate = number_format($rate, 2);
        $this->assign('up_down', $rate > 0 ? 'up' : 'down');
        $this->assign('rate', $rate . '%');
        $id = 'id' . md5(json_encode($this->getData()));
        $this->assign('id', $id);
        # class
        $this->assign('class', $rate > 0 ? 'text-success' : 'text-danger');
        $options_name = 'radialoptions' . $id;
        $chart_name   = 'radialchart' . $id;
        # 向footer中注入js脚本
        $this->getFooter()->append('Weline_Admin::head',
            "<script>
// 添加footer脚本
let {$options_name} = {
    series: [{$rate}],
    chart: {
        type: 'radialBar',
        width: 72,
        height: 72,
        sparkline: {
            enabled: true
        }
    },
    dataLabels: {
        enabled: false
    },
    colors: ['#0ab39c'],
    stroke: {
        lineCap: 'round'
    },
    plotOptions: {
        radialBar: {
            hollow: {
                margin: 0,
                size: '70%'
            },
            track: {
                margin: 0,
            },

            dataLabels: {
                name: {
                    show: false
                },
                value: {
                    offsetY: 5,
                    show: true
                }
            }
        }
    }
};

    var {$chart_name} = new ApexCharts(document.querySelector('#{$id}'), {$options_name});
{$chart_name}.render();
                </script>"
        );
    }
}
