import $ from "jquery";
import ModalFactory from "core/modal_factory";
import Webcam from "./webcam";
import Ajax from "core/ajax";

export const init = (studentid) => {
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
      })
      .fail(Notification.exception);
    // end of ajax call

    ModalFactory.create({
      type: ModalFactory.types.SAVE_CANCEL,
      title: "Turn on webcam",
      body: `
      <p>webcam will be turned on to take video and image input for your attendance</p>
      <button id='start-webcam' class="btn btn-primary">Start Webcam</button>
      <button id='stop-webcam' class="btn btn-secondary">Cancel</button>
      <video id="webcam" autoplay playsinline width="300" height="225"></video>
      <img id="st-image" style="display: none;"/>
      <canvas id="canvas" class="d-none"></canvas>`,
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

      $("#start-webcam").on("click", function () {
        webcam
          .start()
          .then((result) => {
            window.console.log("webcam started");
          })
          .catch((err) => {
            window.console.log(err);
          });

        setTimeout(() => {
          // getting image
          if (!st_img) {
            let context = canvasElement.getContext("2d");
            context.clearRect(0, 0, studentimg.width, studentimg.height);
            context.drawImage(
              studentimg,
              0,
              0,
              studentimg.width,
              studentimg.height
            );
            st_img = canvasElement.toDataURL("image/png");
          }

          var image = webcam.snap();

          // ajax call
          let wsfunction =
            "block_face_recognition_student_attendance_face_recog_api";
          let params = {
            studentimg: st_img_url,
            webcampicture: image,
          };
          let request = {
            methodname: wsfunction,
            args: params,
          };

          Ajax.call([request])[0]
            .done(function (value) {
              let result = value["confidence"];
              window.console.log(result);
            })
            .fail(function (err) {
              window.console.log(err);
            });
          // end of ajax call
        }, 2000);
      });
      $("#stop-webcam").on("click", function () {
        webcam.stop();
        window.location.href = $(location).attr("href");
      });
    });
  });
};
