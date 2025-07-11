<template>
  <div ref="chart" style="width: 100%;height:400px;"></div>
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
import { LineChart, PieChart } from 'echarts/charts';
import { UniversalTransition } from 'echarts/features';
import { CanvasRenderer } from 'echarts/renderers';

echarts.use([
  TitleComponent,
  ToolboxComponent,
  TooltipComponent,
  GridComponent,
  LegendComponent,
  LineChart,
  PieChart,
  CanvasRenderer,
  UniversalTransition
]);

export default {
  name: "StatChart",
  props: {
    type: {
      type: String,
      required: true,
    },
    title: {
      type: String,
      required: true,
    },
  },
  computed: {
    loading(){
      return !this.$store.state[this.type].loaded
    }
  },
  methods: {
    displayChart(){
      var myChart = echarts.init(this.$refs.chart);
      var option;

      if( this.type == 'invitations' ){
        const time = [5,4,3,2,1,0].map(i => this.$dayjs().startOfDay().subtract(i, "week"))
        time[time.length-1] = this.$dayjs().startOfDay().add(1, "day").subtract(1, "minute");

        const labels = ['Expirée', 'Désactivée', 'En attente', 'Utilisée'];
        let series = labels.map(name => {
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
                if( invitation.status == 'expired' ){
                  series[0].data[i-1]++;
                } else if( invitation.status == 'canceled' ){
                  series[1].data[i-1]++;
                } else {
                  series[3].data[i-1]++;
                }
              } else {
                series[2].data[i-1]++;
              }
              break;
            }
          }
        })

        option = {
          title: {
            text: this.title
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
          legend: { data: labels, top: 30 },
          color: ['#000','#ee6666', '#fac858', '#91cc75'],
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

      } else if(this.type == 'communities'){
        let total = this.$store.state.communities.data.reduce((acc, community) => acc + community.approved_users_count , 0)
        let series = [{
          name: this.title,
          type: 'pie',
          radius: ['40%', '70%'],
          avoidLabelOverlap: false,
          label: {
            show: false,
            position: 'center'
          },
          emphasis: {
            label: {
              show: true,
              fontSize: 40,
              fontWeight: 'bold'
            }
          },
          labelLine: {
            show: false
          },
          data: this.$store.state.communities.data
            .filter(community => community.approved_users_count > 1)
            .map(community => ({
              value: community.approved_users_count,
              name: `${community.name} (${Math.round(community.approved_users_count/total*10000)/100}%)`,
            }))
        }]
        option = {
          title: {
            text: this.title
          },
          tooltip: {
            trigger: 'item'
          },
          legend: {
            top: '30',
            orient: 'vertical',
            left: 'left',
            // show only groups with more than 2 users in the legend
            data: series[0].data.filter(d => d.value > 2),
          },
          series,
        };
      }
      option && myChart.setOption(option);
    }
  },
  mounted() {
    if(!this.loading) {
      this.displayChart()
    }
  },
  watch: {
    loading() {
      if(!this.loading) {
        this.displayChart()
      }
    }
  }
}
</script>
