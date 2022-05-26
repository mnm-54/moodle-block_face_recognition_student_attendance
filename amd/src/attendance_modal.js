import $ from "jquery";
import ModalFactory from "core/modal_factory";
import Webcam from "./webcam";
import Ajax from "core/ajax";

export const init = (studentid) => {
  window.console.log("we have been started");
  window.console.log(studentid);
  $(".action-modal").on("click", function () {
    let st_img_url = "";
    let course_id = $(this).attr("id");
    window.console.log(course_id);

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
      <video id="webcam" autoplay playsinline width="640" height="480"></video>
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
      // getting image
      let context = canvasElement.getContext("2d");
      context.clearRect(0, 0, studentimg.width, studentimg.height);
      context.drawImage(studentimg, 0, 0, studentimg.width, studentimg.height);
      let st_img = canvasElement.toDataURL("image/png");
      window.console.log(st_img);

      let webcam = new Webcam(webcamElement, "user", canvasElement);

      $("#start-webcam").on("click", function () {
        webcam
          .start()
          .then((result) => {
            window.console.log("webcam started", result);
          })
          .catch((err) => {
            window.console.log(err);
          });

        setInterval(() => {
          var image = webcam.snap();
          // fetch("http://13.229.125.223:5000/verify")
          //   .then((res) => {
          //     if (res.ok) {
          //       window.console.log("success");
          //     } else {
          //       window.console.log("failure in connection");
          //     }
          //     res.json();
          //   })
          //   .then((data) => {
          //     window.console.log(data);
          //   });
        }, 1000);
      });
      $("#stop-webcam").on("click", function () {
        webcam.stop();
        window.location.href = $(location).attr("href");
      });
    });
  });
};
