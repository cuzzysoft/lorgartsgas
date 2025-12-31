$(document).ready(function () {
    document.getElementById("footerdate").textContent = new Date().getFullYear();
    sidebar();
    loadBankAccounts();
    loadCustomers();
    loadProductPrice(5);
    setCurrentDateTime();

});





function handleSelectChange(event) {
    const selectedValue = event.target.value;
    document.getElementById("lpg_qty").value = "";
    document.getElementById("lpg_price").value = "";
}

function handleKeyUp(event) {
    let qty = document.getElementById("lpg_qty").value;
    var response = $("#unit_pricefill").val();

    document.getElementById("lpg_price").value = (qty * response).toLocaleString();
}

function handlePriceKeyUp(event) {
    var response = $("#unit_pricefill").val();
    let pri = document.getElementById("lpg_price").value;
    pri = pri.replace(/,/g, '');
    document.getElementById("lpg_qty").value = (pri / response).toFixed(2);

}

function showSelAcc(event) {
    let selectedValue = event.target.value;
    let div = document.getElementById('sel_accdiv');
    let select = document.getElementById('sel_acc');

    if (selectedValue === "Cash" || selectedValue === "Unpaid" ) {
        div.style.display = 'none'; // Hide the div
        select.removeAttribute('required'); // Remove the required attribute
    } else {
        div.style.display = 'block'; // Show the div
        select.setAttribute('required', ''); // Add the required attribute
    }
}

function getCurrentPage() {
    var path = window.location.pathname;
    var filename = path.substring(path.lastIndexOf('/') + 1);
    if (filename === '') filename = 'index.php';
    return filename;
}




function setSidebar() {
    var current = getCurrentPage();
    if (current === 'index.html' || current === '/index.html' || current === '') {
        $('#dashboard').addClass('active');
    } else {
        $('#dashboard').removeClass('active');
    }

    if (current === 'suppliers.html') {
        $('#suppliers').addClass('active');
    } else {
        $('#suppliers').removeClass('active');
    }

    if (current === 'sales.html') {
        $('#sales').addClass('active');
    } else {
        $('#sales').removeClass('active');
    }

    if (current === 'unpaid.html') {
        $('#unpaid').addClass('active');
    } else {
        $('#unpaid').removeClass('active');
    }
    if (current === 'revenue_analysis.html') {
        $('#revenue_analysis').addClass('active');
    } else {
        $('#revenue_analysis').removeClass('active');
    }

    if (current === 'expense.html') {
        $('#expense').addClass('active');
    } else {
        $('#expense').removeClass('active');
    }

    if (current === 'purchases.html') {
        $('#purchases').addClass('active');
    } else {
        $('#purchases').removeClass('active');
    }

    if (current === 'products.html') {
        $('#products').addClass('active');
    } else {
        $('#products').removeClass('active');
    }


    if (current === 'etype.html') {
        $('#etype').addClass('active');
    } else {
        $('#etype').removeClass('active');
    }

    if (current === 'tanks.html') {
        $('#tanks').addClass('active');
    } else {
        $('#tanks').removeClass('active');
    }

}

function setCurrentDateTime() {
    const input = document.getElementById('selldate');
    if (!input) return;

    const now = new Date();

    // Convert to local ISO format: YYYY-MM-DDTHH:MM
    const local = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
        .toISOString()
        .slice(0, 16);

    input.value = local;
}

function sidebar() {
    fetch('aside2.html')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            document.getElementById('sidebar-container').innerHTML = html;
            setSidebar();
        })
        .catch(error => {
            console.error('Error loading aside.html:', error);
        });
}


function loadBankAccounts() {
    $.ajax({
        url: 'https://lorgartsgas.hotelsoft.ng/php/get_accounts.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                let options = '<option value="">--select--</option>';

                response.data.forEach(acc => {
                    options += `
                        <option value="${acc.id}">
                          ${acc.accountname} - (${acc.bankname} ${acc.accountno})
                        </option>`;
                });

                $('#sel_acc').html(options);
            } else {
                alert('Failed to load accounts');
            }
        },
        error: function () {
            alert('Error loading bank accounts');
        }
    });
}


function loadCustomers() {
    $.ajax({
        url: 'https://lorgartsgas.hotelsoft.ng/php/get_customers.php',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                let options = '<option value="">--select customer--</option>';

                response.data.forEach(cust => {
                    options += `
                        <option value="${cust.id}">
                          ${cust.company}
                        </option>`;
                });

                $('#customer').html(options);
            } else {
                alert('Failed to load customers');
            }
        },
        error: function () {
            alert('Error loading customers');
        }
    });
}


$("#sellform").on('submit', function (e) {
    e.preventDefault();
    $("#selsubmit").attr("disabled", true);
    $("#selsubmit").text("Submitting...");
    $.ajax({
        url: 'https://lorgartsgas.hotelsoft.ng/php/sell.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: "json",
        success: function (response) {
            console.log(response);
            if (response.status === "Success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Sale made successfully.',
                    confirmButtonText: 'OK'
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed!',
                    text: 'Failed to make sale.',
                    confirmButtonText: 'OK'
                });
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.log(`AJAX Error: ${textStatus} - ${errorThrown}`);
            //     $("#selsubmit").attr("disabled", false);
            // $("#selsubmit").text("Submit");
        }
    });

});


function loadProductPrice(productId) {
    $.ajax({
        url: 'https://lorgartsgas.hotelsoft.ng/php/get_product_price.php',
        type: 'GET',
        data: { product_id: productId },
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success') {
                $('#unit_pricefill').val(response.price);
            } else {
                //alert(response.msg);
            }
        },
        error: function () {
            alert('Error fetching product price');
        }
    });
}

// Example: load product ID 5 when modal opens
$('#sell-modal').on('shown.bs.modal', function () {
    loadProductPrice(5);
});


document.getElementById('logoutBtn').addEventListener('click', function (e) {
    e.preventDefault();

    // Optional: Show confirmation
    if (confirm('Are you sure you want to logout?')) {
        logout();
    }
});

function logout() {
    // Clear all localStorage data
    localStorage.clear();

    // Clear all sessionStorage data
    sessionStorage.clear();

    // Clear any cookies (if you're using them)
    clearAllCookies();

    // Redirect to login page
    window.location.href = 'login.html';
}

function clearAllCookies() {
    const cookies = document.cookie.split(";");

    for (let i = 0; i < cookies.length; i++) {
        const cookie = cookies[i];
        const eqPos = cookie.indexOf("=");
        const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
        document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/";
    }
}