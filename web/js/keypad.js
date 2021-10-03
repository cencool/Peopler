let dispInit = true;
let decPoint = false;
let isNegative = false;

$('#C').on('click', function() {
    init();
});
$('#1').on('click', function() {
    clear();
    document.getElementById('disp').textContent += '1';
});
$('#2').on('click', function() {
    clear();
    document.getElementById('disp').textContent += '2';
});
$('#3').on('click', function() {
    clear();
    document.getElementById('disp').textContent += '3';
});
$('#4').on('click', function() {
    clear();
    document.getElementById('disp').textContent += '4';
});
$('#5').on('click', function() {
    clear();
    document.getElementById('disp').textContent += '5';
});
$('#6').on('click', function() {
    clear();
    document.getElementById('disp').textContent += '6';
});
$('#7').on('click', function() {
    clear();
    document.getElementById('disp').textContent += '7';
});
$('#8').on('click', function() {
    clear();
    document.getElementById('disp').textContent += '8';
});
$('#9').on('click', function() {
    clear();
    document.getElementById('disp').textContent += '9';
});
$('#0').on('click', function() {
    clear();
    document.getElementById('disp').textContent += '0';
});
$('#decPoint').on('click', function() {
    clear();
    if (!decPoint) {
        document.getElementById('disp').textContent += '.';
        decPoint = true;
    }
});
$('#negative').on('click', function() {
    if (!isNegative) {
        let a = document.getElementById("disp").textContent;
        document.getElementById("disp").textContent =
            "-" + a;
        isNegative = true;
    } else {
        document.getElementById("disp").textContent = document
            .getElementById("disp")
            .textContent.substr(1);
        isNegative = false;
    }
});

$('#result').on('click', sendRequest);

function sendRequest() {

    $.ajax({
        url: 'http://localhost:8000?r=calc/ajax',
        data: 'a=1',
        method: 'POST',
        success: function(response) {
            alert(response);
        }
    });

}

function clear() {
    if (dispInit) {
        document.getElementById('disp').textContent = '';
        dispInit = false;
        decPoint = false;
        isNegative = false;
    }
}

function init() {
    document.getElementById('disp').textContent = '0.';
    dispInit = true;
}