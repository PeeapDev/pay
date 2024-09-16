"use strict";

var deadline = expireTime;

var x = setInterval(function () {
    var now = Date.now();
    var t = deadline - now;

    var minutes = Math.floor((t % (1000 * 60 * 60)) / (1000 * 60));
    var seconds = Math.floor((t % (1000 * 60)) / 1000);

    var bar = document.querySelector('#progressBar');
    var totalSeconds = expireSec,
        remainingSeconds = t * 0.001
        bar.style.width = (remainingSeconds * 100 / totalSeconds) + "%";

    document.getElementById("timer").innerHTML = minutes + "m " + ":" + seconds + "s ";
    if (t < 0) {
        clearInterval(x);
        bar.style.width = 0 + "%";
        document.getElementById("timer").innerHTML = expireText;
        $(".submit-button").attr("disabled", true);
        $("#pay_with").attr("disabled", true);
        $("#receive_with").attr("disabled", true);
    }
}, 1000);
