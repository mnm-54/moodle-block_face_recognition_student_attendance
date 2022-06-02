import $ from "jquery";
import ModalFactory from "core/modal_factory";
import Webcam from "./webcam";
import Ajax from "core/ajax";
export const init = (studentid, successmessage, failedmessage) => {
  $(".action-modal").on("click", function () {
    let st_img_url = "";
    let course_id = $(this).attr("id");

    // ajax call
    let wsfunction = "block_face_recognition_student_attendance_image_api";
    let params = {
      courseid: course_id,
      studentid: studentid,
    };
    let request = {
      methodname: wsfunction,
      args: params,
    };

    Ajax.call([request])[0]
      .done(function (value) {
        st_img_url = value["image_url"];
        create_modal();
      })
      .fail(Notification.exception);
    // end of ajax call

    function create_modal() {
      ModalFactory.create({
        type: ModalFactory.types.SAVE_CANCEL,
        title: "Turn on webcam",
        body: `
        <p>webcam will be turned on to take video and image input for your attendance</p>
        <button id='start-webcam' class="btn btn-primary">Start Webcam</button>
        <button id='stop-webcam' class="btn btn-secondary">Cancel</button>
        <video id="webcam" autoplay playsinline width="300" height="225"></video>
        <canvas id="canvas" class="d-none"></canvas>
        <img id="st-image" style="display: none;"/>
        <button id="submit-attendance" style="display:none;" class="btn btn-primary">Submit attendance</button>
        <button id="try-again" style="display:none;" class="btn btn-primary">Try again</button>
        <div id="message"></div>`,
      }).then(function (modal) {
        modal.setSaveButtonText("Start Webcam");

        modal.show();
        $(".modal-footer").hide();

        const webcamElement = document.getElementById("webcam");
        const canvasElement = document.getElementById("canvas");
        let studentimg = document.getElementById("st-image");
        studentimg.src = st_img_url;
        let st_img = "";

        let webcam = new Webcam(webcamElement, "user", canvasElement);

        $(".close").on("click", function () {
          webcam.stop();
          window.location.href = $(location).attr("href");
        });

        function getDataUrl(studentimg) {
          const canvas = document.createElement("canvas");
          const ctx = canvas.getContext("2d");
          // Set width and height
          canvas.width = studentimg.width;
          canvas.height = studentimg.height;
          // Draw the image
          ctx.drawImage(studentimg, 0, 0);
          return canvas.toDataURL("image/png");
        }
        function displaySubmitAttendance() {
          document.getElementById("submit-attendance").style.display = "block";
        }
        function hideSubmitAttendance() {
          document.getElementById("submit-attendance").style.display = "none";
        }
        function displayTryAgain() {
          document.getElementById("try-again").style.display = "block";
        }
        function hideTryAgain() {
          document.getElementById("try-again").style.display = "none";
        }
        function removeMessages() {
          const message = document.getElementById("message");
          while (message.hasChildNodes()) {
            message.removeChild(message.firstChild);
          }
        }
        function displaySuccessMessage() {
          hideSubmitAttendance();
          displayMessage(successmessage, 1);
        }
        function displayFailedMessage() {
          hideSubmitAttendance();
          displayTryAgain();
          displayMessage(failedmessage, 0);
        }
        function displayMessage(message, flag) {
          var spn = document.createElement("span");
          spn.textContent = message + ".";
          spn.setAttribute("class", flag ? "text-success" : "text-danger");
          document.getElementById("message").appendChild(spn);
        }
        function logAttendance() {
          let wsfunction =
            "block_face_recognition_student_attendance_update_db";
          let params = {
            courseid: course_id,
            studentid: studentid,
          };
          let request = {
            methodname: wsfunction,
            args: params,
          };

          Ajax.call([request])[0]
            .done(function () {
              window.location.href = $(location).attr("href");
            })
            .fail(Notification.exception);
        }
        function submitAttendance(st_img, image) {
          let wsfunction =
            "block_face_recognition_student_attendance_face_recog_api";
          let params = {
            studentimg: st_img,
            webcampicture: image,
          };
          let request = {
            methodname: wsfunction,
            args: params,
          };

          Ajax.call([request])[0]
            .done(function (value) {
              let result = value["confidence"];
              window.console.log(value);

              if (result >= 0.6) {
                window.console.log("Success");
                displaySuccessMessage();
                logAttendance();
                setTimeout(() => {
                  window.location.href = $(location).attr("href");
                }, 1000);
              } else {
                displayFailedMessage();
              }
            })
            .fail(function (err) {
              window.console.log(err);
            });
          // end of ajax call
        }
        $("#start-webcam").on("click", function () {
          webcam
            .start()
            .then((result) => {
              displaySubmitAttendance();

              $("#submit-attendance").on("click", function () {
                removeMessages();
                if (!st_img) {
                  st_img = getDataUrl(studentimg);
                }
                let image = webcam.snap();
                submitAttendance(st_img, image);
              });
              $("#try-again").on("click", function () {
                removeMessages();
                hideTryAgain();
                document.getElementById("submit-attendance").click();
              });
            })
            .catch((err) => {
              window.console.log(err);
            });
        });
        $("#stop-webcam").on("click", function () {
          webcam.stop();
          window.location.href = $(location).attr("href");
        });
      });
    }
  });
};
