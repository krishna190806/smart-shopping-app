const datePicker = document.getElementById('date-picker');
        const currentDateSpan = document.getElementById('date-span');

        function updateDate() {
            const selectedDate = datePicker.value;
            currentDateSpan.innerText = selectedDate;
        }

        const currentDate = new Date().toISOString().split('T')[0];
        datePicker.value = currentDate;
        currentDateSpan.innerText = currentDate;

        datePicker.addEventListener('change', updateDate);