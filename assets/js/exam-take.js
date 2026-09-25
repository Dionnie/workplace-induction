/**
 * The inductee's exam page (views/inductee/exams/take.php): keeps the
 * answered count in the bar at the bottom, and asks before leaving the page
 * with answers that haven't been submitted.
 */
(function () {
    'use strict';

    var form = document.querySelector('[data-exam-form]');
    if (!form) {
        return;
    }

    var status = form.querySelector('[data-exam-progress]');
    var total = form.querySelectorAll('.exam-question').length;
    var submitting = false;

    function answered() {
        return form.querySelectorAll('.exam-choice-radio:checked').length;
    }

    function update() {
        var count = answered();
        status.textContent = count === total ? 'All ' + total + ' answered' : count + ' of ' + total + ' answered';
        status.classList.toggle('is-complete', count === total);
    }

    form.addEventListener('change', update);

    // Only fires once the browser's own check (every question answered) passes.
    form.addEventListener('submit', function () {
        submitting = true;
    });

    window.addEventListener('beforeunload', function (e) {
        if (!submitting && answered() > 0) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // The browser may restore answers on Back or reload.
    update();
})();
