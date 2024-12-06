@extends('layouts/default')
{{-- Page title --}}
@section('title')
Location wise chart 
@parent
@stop


{{-- Page content --}}
@section('content')
<div class="row" style="margin-bottom: 15px;">
    <div class="col-md-3">
        <select class="form-select" id="location-select">
            <option value="">Select</option>
            @foreach ($locations as $location)
            <option value="{{$location->id}}">{{$location->name}}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">
                    Asset Labelling Status 
                </h2>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                        <x-icon type="minus" />
                        <span class="sr-only">{{ trans('general.collapse') }}</span>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="chart-responsive">
                            <canvas id="assetLabellingStatusGraph" height="260"></canvas>
                        </div> 
                    </div> 
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">
                    Asset Status 
                </h2>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                        <x-icon type="minus" />
                        <span class="sr-only">{{ trans('general.collapse') }}</span>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="chart-responsive">
                            <canvas id="assetStatusGraph" height="260"></canvas>
                        </div> 
                    </div> 
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">
                    Asset Categories 
                </h2>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                        <x-icon type="minus" />
                        <span class="sr-only">{{ trans('general.collapse') }}</span>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="chart-responsive">
                            <canvas id="assetCategoriesGraph" height="260"></canvas>
                        </div> 
                    </div> 
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">
                    Warranty Status
                </h2>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                        <x-icon type="minus" />
                        <span class="sr-only">{{ trans('general.collapse') }}</span>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="chart-responsive">
                            <canvas id="warrantyStatusGraph" height="260"></canvas>
                        </div> 
                    </div> 
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="box box-default">
            <div class="box-header with-border">
                <h2 class="box-title">
                    Manufacturers
                </h2>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse" aria-hidden="true">
                        <x-icon type="minus" />
                        <span class="sr-only">{{ trans('general.collapse') }}</span>
                    </button>
                </div>
            </div>
            <!-- /.box-header -->
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="chart-responsive">
                            <canvas id="manufacturersGraph" height="260"></canvas>
                        </div> 
                    </div> 
                </div>
            </div>
        </div>
    </div>

@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['simple_view' => true, 'nopages' => true])
@stop

@push('js')
<script>
    $(document).ready(function(){
        $('#location-select').on('change', function(){
            let locationId =  $(this).val();
            populateGraphs(locationId);
        });

        function populateGraphs(location){
            $.ajax({
                type: "GET",
                url: "{{ route('get.location.wise.graph') }}",
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                data: {'location_id':location},
                dataType: "json",
                success: function (response) {
                    console.log(response);
                    assetLabellingChartRender(response.asset_labelling_status_data);
                    assetStatusChartRender(response.asset_status_data);
                    assetCategoriesChartRender(response.asset_category_data);
                    warrantyStatusGraph(response.warranty_status_data);
                    manufacturersGraph(response.manufacturers_data);
                }
            });
        }

        function assetLabellingChartRender(data = []){
            var assetLabellingPieChart = $("#assetLabellingStatusGraph").get(0).getContext("2d");
            var assetLabellingPieChart = new Chart(assetLabellingPieChart);
            var assetLabellingPieChartCtx = document.getElementById("assetLabellingStatusGraph");
            var assetLabellingPieChartOptions = {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        enabled: true
                    }
                }
            };
            var myassetLabellingPieChart = new Chart(assetLabellingPieChartCtx,{
                type   : 'pie',
                data   : data,
                options: assetLabellingPieChartOptions
            });
        }

        function assetStatusChartRender(data = []){
            var assetStatusPieChart = $("#assetStatusGraph").get(0).getContext("2d");
            var assetStatusPieChart = new Chart(assetStatusPieChart);
            var assetStatusPieChartCtx = document.getElementById("assetStatusGraph");
            var assetStatusPieChartOptions = {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        enabled: true
                    }
                }
            };
            var myassetStatusPieChart = new Chart(assetStatusPieChartCtx,{
                type   : 'pie',
                data   : data,
                options: assetStatusPieChartOptions
            });
        }

        function assetCategoriesChartRender(data = []){
            var assetCategoriesPieChart = $("#assetCategoriesGraph").get(0).getContext("2d");
            var assetCategoriesPieChart = new Chart(assetCategoriesPieChart);
            var assetCategoriesPieChartCtx = document.getElementById("assetCategoriesGraph");
            var assetCategoriesPieChartOptions = {
                responsive: true,
                maintainAspectRatio: true,
                legend: {
                    position: 'top', // Position of the legend
                },
                cutoutPercentage: 50, // Adjust the size of the doughnut's hole (50% default)
                tooltips: {
                    callbacks: {
                        label: function (tooltipItem, data) {
                            var dataset = data.datasets[tooltipItem.datasetIndex];
                            var total = dataset.data.reduce((sum, value) => sum + value, 0);
                            var value = dataset.data[tooltipItem.index];
                            var percentage = ((value / total) * 100).toFixed(2);
                            return `${data.labels[tooltipItem.index]}: ${value} (${percentage}%)`;
                        }
                    }
                }
            };
            var myassetCategoriesPieChart = new Chart(assetCategoriesPieChartCtx,{
                type   : 'doughnut',
                data   : data,
                options: assetCategoriesPieChartOptions
            });
        }

        function warrantyStatusGraph(data = []){
            var warrantyStatusbarChart = $("#warrantyStatusGraph").get(0).getContext("2d");
            var warrantyStatusbarChart = new Chart(warrantyStatusbarChart);
            var warrantyStatusbarChartCtx = document.getElementById("warrantyStatusGraph");
            var warrantyStatusbarChartOptions = {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false // Hide X-axis gridlines
                        },
                        barPercentage: 0.5, // Adjust bar width
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true // Start Y-axis at 0
                        },
                        gridLines: {
                            display: true // Show Y-axis gridlines
                        }
                    }]
                },
                legend: {
                    position: 'top', // Position of the legend
                }
            };
            var mywarrantyStatusbarChart = new Chart(warrantyStatusbarChartCtx,{
                type   : 'bar',
                data   : data,
                options: warrantyStatusbarChartOptions
            });
        }

        function manufacturersGraph(data = []){
            var manufacturersChart = $("#manufacturersGraph").get(0).getContext("2d");
            var manufacturersChart = new Chart(manufacturersChart);
            var manufacturersChartCtx = document.getElementById("manufacturersGraph");
            var manufacturersChartOptions = {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    xAxes: [{
                        gridLines: {
                            display: false // Hide X-axis gridlines
                        },
                        barPercentage: 0.5, // Adjust bar width
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true // Start Y-axis at 0
                        },
                        gridLines: {
                            display: true // Show Y-axis gridlines
                        }
                    }]
                },
                legend: {
                    position: 'top', // Position of the legend
                }
            };
            var mymanufacturersChart = new Chart(manufacturersChartCtx,{
                type   : 'bar',
                data   : data,
                options: manufacturersChartOptions
            });
        }
        
    });
</script>

@endpush
