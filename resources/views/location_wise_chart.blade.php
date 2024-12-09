@extends('layouts/default')
{{-- Page title --}}
@section('title')
Location wise insights 
@parent
@stop


{{-- Page content --}}
@section('content')
<div class="row" style="margin-bottom: 15px;">
    <div class="col-md-3">
        <select class="form-select" id="location-select">
            <option value="">Select</option>
            @foreach ($locations as $location)
            <option value="{{$location->id}}" @selected($location->id == 2)>{{$location->name}}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="row">
    {{-- <div class="col-md-4">
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
    </div> --}}

    <div class="col-md-4">
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

    <div class="col-md-4">
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

    <div class="col-md-4">
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

    <div class="col-md-4">
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
  $(document).ready(function () {
    // Store chart instances globally
    let assetLabellingChartInstance = null;
    let assetStatusChartInstance = null;
    let assetCategoriesChartInstance = null;
    let warrantyStatusChartInstance = null;
    let manufacturersChartInstance = null;

    // Initial graph population
    populateGraphs($('#location-select').val());

    // Handle location selection change
    $('#location-select').on('change', function () {
        let locationId = $(this).val();
        populateGraphs(locationId);
    });

    // Fetch and render graphs
    function populateGraphs(location) {
        $.ajax({
            type: "GET",
            url: "{{ route('get.location.wise.graph') }}",
            headers: {
                "X-Requested-With": 'XMLHttpRequest',
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
            },
            data: { 'location_id': location },
            dataType: "json",
            success: function (response) {
                console.log(response);

                // Render each chart after destroying previous instances
                //assetLabellingChartRender(response.asset_labelling_status_data);
                assetStatusChartRender(response.asset_status_data);
                assetCategoriesChartRender(response.asset_category_data);
                warrantyStatusGraph(response.warranty_status_data);
                manufacturersGraph(response.manufacturers_data);
            }
        });
    }

    function assetLabellingChartRender(data = []) {
        if (assetLabellingChartInstance) {
            assetLabellingChartInstance.destroy(); // Destroy previous instance
        }
        var ctx = document.getElementById("assetLabellingStatusGraph").getContext("2d");
        assetLabellingChartInstance = new Chart(ctx, {
            type: 'pie',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { enabled: true }
                }
            }
        });
    }

    function assetStatusChartRender(data = []) {
        if (assetStatusChartInstance) {
            assetStatusChartInstance.destroy();
        }
        var ctx = document.getElementById("assetStatusGraph").getContext("2d");
        var chartData = {
            labels: data.labels, // Status labels
            datasets: [{
                label: 'Quantity',
                backgroundColor: data.datasets[0].backgroundColor, // Colors from backend
                data: data.datasets[0].data, // Quantities
            }],
            ids: data.ids // Include `status_id` array
        };

        assetStatusChartInstance = new Chart(ctx, {
            type: 'pie',
            data: chartData, // Use chartData including `ids`
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: { enabled: true }
                },
                onClick: function (event, elements) {
                    if (elements.length > 0) {
                        var chartElement = elements[0];
                        var index = chartElement._index; // Use `_index` for the clicked element
                        var statusId = chartData.ids[index]; // Retrieve the corresponding `status_id`
                        if (statusId) {
                            window.location.href = `/hardware?status_id=${statusId}`; // Redirect with dynamic `status_id`
                        } else {
                            window.location.href = `/statuslabels?`;
                        }
                    }
                }
            }
        });

    }

    function assetCategoriesChartRender(data = []) {
        if (assetCategoriesChartInstance) {
            assetCategoriesChartInstance.destroy();
        }
        var ctx = document.getElementById("assetCategoriesGraph").getContext("2d");
        var chartData = {
            labels: data.labels, // Labels (category names)
            datasets: [{
                label: 'Quantity',
                backgroundColor: data.datasets[0].backgroundColor,
                data: data.datasets[0].data, // Counts
            }],
            ids: data.ids // Add `ids` here
        };
        assetCategoriesChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: chartData, // Use chartData that includes `ids`
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
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
                },
                onClick: function (event, elements) {
                    if (elements.length > 0) {
                        var chartElement = elements[0];
                        var index = chartElement._index; // Index of the clicked element
                        var categoryId = chartData.ids[index]; // Use `chartData.ids` here
                        console.log("Clicked index:", index, "Category ID:", categoryId); // Debugging
                        if (categoryId) {
                            window.location.href = `/categories/${categoryId}`; // Redirect with dynamic ID
                        }
                    }
                }
            }
        });
    }

    function warrantyStatusGraph(data = []) {
        if (warrantyStatusChartInstance) {
            warrantyStatusChartInstance.destroy();
        }
        var ctx = document.getElementById("warrantyStatusGraph").getContext("2d");
        warrantyStatusChartInstance = new Chart(ctx, {
            type: 'bar',
            data: data,
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    xAxes: [{
                        gridLines: { display: false },
                        barPercentage: 0.5
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true, // Ensures the Y-axis starts at 0
                            min: 0 // Explicitly set the minimum Y-axis value to 0
                        },
                        gridLines: { display: true }
                    }]
                },
                plugins: { legend: { position: 'top' } },
                legend: {
                    display: false // Disable the legend
                }
            }
        });
    }

    function manufacturersGraph(data = []) {
        if (manufacturersChartInstance) {
            manufacturersChartInstance.destroy();
        }
        var ctx = document.getElementById("manufacturersGraph").getContext("2d");
        var chartData = {
            labels: data.labels, // Manufacturer names
            datasets: [{
                label: 'Quantity',
                backgroundColor: data.datasets[0].backgroundColor, // Colors from backend
                data: data.datasets[0].data, // Counts
            }],
            ids: data.ids // Include manufacturer IDs
        };

        manufacturersChartInstance = new Chart(ctx, {
            type: 'bar',
            data: chartData, // Use chartData including `ids`
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    xAxes: [{
                        gridLines: { display: false },
                        barPercentage: 0.5
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true, // Ensures the Y-axis starts at 0
                            min: 0 // Explicitly set the minimum Y-axis value to 0
                        },
                        gridLines: { display: true }
                    }]
                },
                plugins: { legend: { position: 'top' } },
                legend: {
                    display: false // Disable the legend
                },
                onClick: function (event, elements) {
                    if (elements.length > 0) {
                        var chartElement = elements[0];
                        var index = chartElement._index; // Use `_index` for the clicked element
                        var manufacturerId = chartData.ids[index]; // Retrieve the corresponding `manufacturer_id`
                        console.log("Clicked index:", index, "Manufacturer ID:", manufacturerId); // Debugging
                        if (manufacturerId) {
                            window.location.href = `/manufacturers/${manufacturerId}`; // Redirect with dynamic `manufacturer_id`
                        } else {
                            window.location.href = `/manufacturers`;
                        }
                    }
                }
            }
        });

    }
});

</script>

@endpush
