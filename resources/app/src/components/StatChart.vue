<template>
  <div ref="chart" style="width: 600px;height:400px;"></div>
</template>
<script>
import * as echarts from 'echarts/core';
import {
  TitleComponent,
  ToolboxComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent
} from 'echarts/components';
import { LineChart } from 'echarts/charts';
import { UniversalTransition } from 'echarts/features';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([
  TitleComponent,
  ToolboxComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent,
  LineChart,
  CanvasRenderer,
  UniversalTransition
]);

export default {
  name: "StatChart",
  props: {
    // only invitations type is supported
    type: {
      type: String,
      required: true,
    }
  },
  async mounted() {
    var myChart = echarts.init(this.$refs.chart);
    var option;

    await this.$store.dispatch(`${this.type}/retrieve`);

    const time = [5,4,3,2,1,0].map(i => this.$dayjs().startOfDay().subtract(i, "week"))
    time[time.length-1] = this.$dayjs().startOfDay().add(1, "day").subtract(1, "minute");
    let series = ['En attente', 'Utilisée'].map(name => {
      return {
        name,
        type: 'line',
        stack: 'Total',
        areaStyle: {},
        emphasis: {
          focus: 'series'
        },
        data: [0,0,0,0,0],
      }
    })
    this.$store.state.invitations.data.forEach(invitation => {
      for( let i=1; i<time.length; i++ ){
        if( this.$dayjs(invitation.created_at).isBefore(time[i-1]) ) {
          break;
        } else if(this.$dayjs(invitation.created_at).isBefore(time[i]) ) {
          if( invitation.consumed_at ) {
            series[1].data[i-1]++;
          } else {
            series[0].data[i-1]++;
          }
          break;
        }
      }
    })

    option = {
      title: {
        text: 'Invitations par semaine'
      },
      tooltip: {
        trigger: 'axis',
        axisPointer: {
          type: 'cross',
          label: {
            backgroundColor: '#6a7985'
          }
        }
      },
      legend: {
        data: ['En attente', /*'Renvoyée',*/ 'Utilisée'/*, 'Désactivée', 'Périmée'*/]
      },
      toolbox: {
        feature: {
          saveAsImage: {}
        }
      },
      grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
      },
      xAxis: [
        {
          type: 'category',
          boundaryGap: false,
          data: time.slice(1).map(t => t.format('D MMM'))
        }
      ],
      yAxis: [{type: 'value'}],
      series
    };

    option && myChart.setOption(option);
  }
}
</script>
