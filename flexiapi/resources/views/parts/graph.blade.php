@php($id = generatePin())

<div id="chart-{{ $id }}" class="chart"></div>

<script>
    chart = document.getElementById('chart-' + {{ $id }});
    chart.innerHTML = '';

    canvas = document.createElement('canvas');
    canvas.id = 'myChart-' + {{ $id }};
    chart.appendChild(canvas);

    new Chart(
        document.getElementById('myChart-' + {{ $id }}),
        {!! $jsonConfig !!}
    );
</script>
