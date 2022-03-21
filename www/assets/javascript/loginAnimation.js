

// Get flaoter div
let floater_user;
let floater_client;
let floater_doctor;
let floater_success;

function onLoad() {
    floater_user = document.getElementsByClassName("float-user-child")[0]
    floater_client = document.getElementsByClassName("float-client-child")[0]
    floater_doctor = document.getElementsByClassName("float-doctor-child")[0]
    floater_success = document.getElementsByClassName("float-success-child")[0]
}

function next(grade) {
    onLoad()
    if (grade == "CLIENT") {
        floater_user.classList.remove("prev-animation")
        floater_client.classList.remove("next-animation")

        floater_user.classList.add("next-animation")
        floater_client.classList.add("prev-animation")
    }else if (grade == "DOCTOR") {
        floater_user.classList.remove("prev-animation")
        floater_doctor.classList.remove("next-animation")

        floater_user.classList.add("next-animation")
        floater_doctor.classList.add("prev-animation")
    }
    prev_grade = grade
}

function success(state) {
    onLoad()
    floater_user.classList.remove("prev-animation")
    floater_client.classList.remove("prev-animation")
    floater_doctor.classList.remove("prev-animation")

    if (state == "CLIENT") {
        floater_user.style.width = "0%"
        floater_client.classList.add("next-animation")
    }else if (state == "DOCTOR") {
        floater_user.style.width = "0%"
        floater_doctor.classList.add("next-animation")
    }
    floater_success.classList.add("prev-animation")

}

function prev(grade) {
    onLoad()
    if (grade == "CLIENT") {
        floater_user.classList.remove("next-animation")
        floater_client.classList.remove("prev-animation")

        floater_user.classList.add("prev-animation")
        floater_client.classList.add("next-animation")
    } else if (grade == "DOCTOR") {
        floater_user.classList.remove("next-animation")
        floater_doctor.classList.remove("prev-animation")

        floater_user.classList.add("prev-animation")
        floater_doctor.classList.add("next-animation")
    }

}

function setState(state) {
    console.log("setstate"+state)
    document.addEventListener('DOMContentLoaded', function() {
        onLoad()
        floater_user.classList.remove("next-animation")
        floater_user.classList.remove("prev-animation")
        floater_client.classList.remove("next-animation")
        floater_client.classList.remove("prev-animation")
        floater_doctor.classList.remove("next-animation")
        floater_doctor.classList.remove("prev-animation")
        if (state == "CLIENT") {
            floater_user.style.width = "0%"
            floater_client.style.width = "100%"
            floater_doctor.style.width = "0%"
        }else if (state == "DOCTOR") {
            floater_user.style.width = "0%"
            floater_client.style.width = "0%"
            floater_doctor.style.width = "100%"
        }
        else if (state == "SUCCESS") {
            floater_user.style.width = "0%"
            floater_client.style.width = "0%"
            floater_doctor.style.width = "0%"
            floater_success.style.width = "100%"
        }
    }, false);

}

