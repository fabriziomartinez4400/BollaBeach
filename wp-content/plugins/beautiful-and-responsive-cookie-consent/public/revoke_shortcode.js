(function (element) {
  var cookiedomain = "domain=" + element.dataset.domain;

  if (element.dataset.domain == "") {
    cookiedomain = "";
  }

  element.text = element.dataset.link_text_after_click;
  element.dataset.link_text_after_click = element.dataset.link_text_before_click;
  element.dataset.link_text_before_click = element.text;

  document.cookie =
    element.dataset.cookiename +
    "=" +
    element.dataset.cookie_value_after_click +
    ";expires=" +
    element.dataset.expire +
    ";path=/;" +
    cookiedomain;

  var current_cookie_value = element.dataset.current_cookie_value;
  element.dataset.current_cookie_value = element.dataset.cookie_value_after_click;
  element.dataset.cookie_value_after_click = current_cookie_value;
})(this);
