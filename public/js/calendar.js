(function ($, window, undefined) {
  "use strict";
  $.Calendario = function (options, element) {
    this.$el = $(element);
    this._init(options);
  };

  // the options
  $.Calendario.defaults = {
    weeks: [
      "Понеділок",
      "Вівторок",
      "Середа",
      "Четверг",
      "Пятниця",
      "Субота",
      "Неділя",
    ],
    weekabbrs: ["Нд", "Пн", "Вт", "Ср", "Чт", "Пт", "Сб"],
    months: [
      "Січень",
      "Лютий",
      "Березень",
      "Квітень",
      "Травень",
      "Червень",
      "Липень",
      "Серпень",
      "Вересень",
      "Жовтень",
      "Листопад",
      "Грудень",
    ],
    monthabbrs: [
      "Січ",
      "Лют",
      "Бер",
      "Кві",
      "Тра",
      "Чер",
      "Лип",
      "Сер",
      "Вер",
      "Жов",
      "Лис",
      "Гру",
    ],
    displayWeekAbbr: false,
    displayMonthAbbr: false,
    startIn: 1,
    onDayClick: function ($el, $content, dateProperties) {
      return false;
    },
  };
  $.Calendario.prototype = {
    _init: function (options) {
      // options
      this.options = $.extend(true, {}, $.Calendario.defaults, options);
      this.today = new Date();
      this.month =
        isNaN(this.options.month) || this.options.month == null
          ? this.today.getMonth()
          : this.options.month - 1;
      this.year =
        isNaN(this.options.year) || this.options.year == null
          ? this.today.getFullYear()
          : this.options.year;
      this.caldata = this.options.caldata || {};
      this._generateTemplate();
      this._initEvents();
    },
    _initEvents: function () {
      var self = this;
      this.$el.on("click.calendario", "div.fc-row > div", function () {
        var $cell = $(this),
          idx = $cell.index(),
          $content = $cell.children("div"),
          dateProp = {
            day: $cell.children("span.fc-date").text(),
            month: self.month + 1,
            monthname: self.options.displayMonthAbbr
              ? self.options.monthabbrs[self.month]
              : self.options.months[self.month],
            year: self.year,
            weekday: idx + self.options.startIn,
            weekdayname: self.options.weeks[idx + self.options.startIn],
          };
        if (dateProp.day) {
          self.options.onDayClick($cell, $content, dateProp);
        }
      });
    },
    _generateTemplate: function (callback) {
      var head = this._getHead(),
        body = this._getBody(),
        rowClass;
      switch (this.rowTotal) {
        case 4:
          rowClass = "fc-four-rows";
          break;
        case 5:
          rowClass = "fc-five-rows";
          break;
        case 6:
          rowClass = "fc-six-rows";
          break;
      }
      this.$cal = $('<div class="fc-calendar ' + rowClass + '">').append(
        head,
        body,
      );
      this.$el.find("div.fc-calendar").remove().end().append(this.$cal);
      if (callback) {
        callback.call();
      }
    },
    _getHead: function () {
      var html = '<div class="fc-head">';
      for (var i = 0; i <= 6; i++) {
        var pos = i + this.options.startIn,
          j = pos > 6 ? pos - 6 - 1 : pos;
        html += "<div>";
        html += this.options.displayWeekAbbr
          ? this.options.weekabbrs[j]
          : this.options.weeks[j];
        html += "</div>";
      }
      html += "</div>";
      return html;
    },
    _getBody: function () {
      var d = new Date(this.year, this.month + 1, 0),
        monthLength = d.getDate(),
        firstDay = new Date(this.year, this.month, 1);
      this.startingDay = firstDay.getDay();
      var html = '<div class="fc-body"><div class="fc-row">',
        day = 1;
      for (var i = 0; i < 7; i++) {
        for (var j = 0; j <= 6; j++) {
          var pos = this.startingDay - this.options.startIn,
            p = pos < 0 ? 6 + pos + 1 : pos,
            inner = "",
            today =
              this.month === this.today.getMonth() &&
              this.year === this.today.getFullYear() &&
              day === this.today.getDate(),
            content = "";
          if (day <= monthLength && (i > 0 || j >= p)) {
            inner += '<span class="fc-date">' + day + "</span>";
            var strdate =
                (this.month + 1 < 10
                  ? "0" + (this.month + 1)
                  : this.month + 1) +
                "-" +
                (day < 10 ? "0" + day : day) +
                "-" +
                this.year,
              dayData = this.caldata[strdate];
            if (dayData) {
              content = dayData;
            }
            if (content !== "") {
              inner += "<div>" + content + "</div>";
            }
            ++day;
          }
          var cellClasses = today ? "fc-today " : "";
          if (content !== "") {
            cellClasses += "fc-content";
          }
          html +=
            cellClasses !== "" ? '<div class="' + cellClasses + '">' : "<div>";
          html += inner;
          html += "</div>";
        }

        // stop making rows if we've run out of days
        if (day > monthLength) {
          this.rowTotal = i + 1;
          break;
        } else {
          html += '</div><div class="fc-row">';
        }
      }
      html += "</div></div>";
      return html;
    },
    // based on http://stackoverflow.com/a/8390325/989439
    _isValidDate: function (date) {
      date = date.replace(/-/gi, "");
      var month = parseInt(date.substring(0, 2), 10),
        day = parseInt(date.substring(2, 4), 10),
        year = parseInt(date.substring(4, 8), 10);
      if (month < 1 || month > 12) {
        return false;
      } else if (day < 1 || day > 31) {
        return false;
      } else if (
        (month == 4 || month == 6 || month == 9 || month == 11) &&
        day > 30
      ) {
        return false;
      } else if (
        month == 2 &&
        (year % 400 == 0 || year % 4 == 0) &&
        year % 100 != 0 &&
        day > 29
      ) {
        return false;
      }
      function isLeapYear(year) {
        return (year % 4 === 0 && year % 100 !== 0) || year % 400 === 0;
      }
      return { day: day, month: month, year: year };
    },
    _move: function (period, dir, callback) {
      if (dir === "previous") {
        if (period === "month") {
          this.year = this.month > 0 ? this.year : --this.year;
          this.month = this.month > 0 ? --this.month : 11;
        } else if (period === "year") {
          this.year = --this.year;
        }
      } else if (dir === "next") {
        if (period === "month") {
          this.year = this.month < 11 ? this.year : ++this.year;
          this.month = this.month < 11 ? ++this.month : 0;
        } else if (period === "year") {
          this.year = ++this.year;
        }
      }
      this._generateTemplate(callback);
    },
    getYear: function () {
      return this.year;
    },
    getMonth: function () {
      return this.month + 1;
    },
    getMonthName: function () {
      return this.options.displayMonthAbbr
        ? this.options.monthabbrs[this.month]
        : this.options.months[this.month];
    },
    getCell: function (day) {
      var row = Math.floor((day + this.startingDay - this.options.startIn) / 7),
        pos = day + this.startingDay - this.options.startIn - row * 7 - 1;
      return this.$cal
        .find("div.fc-body")
        .children("div.fc-row")
        .eq(row)
        .children("div")
        .eq(pos)
        .children("div");
    },
    setData: function (caldata) {
      caldata = caldata || {};
      $.extend(this.caldata, caldata);
      this._generateTemplate();
    },
    // goes to today's month/year
    gotoNow: function (callback) {
      this.month = this.today.getMonth();
      this.year = this.today.getFullYear();
      this._generateTemplate(callback);
    },
    // goes to month/year
    goto: function (month, year, callback) {
      this.month = month;
      this.year = year;
      this._generateTemplate(callback);
    },
    gotoPreviousMonth: function (callback) {
      this._move("month", "previous", callback);
    },
    gotoPreviousYear: function (callback) {
      this._move("year", "previous", callback);
    },
    gotoNextMonth: function (callback) {
      this._move("month", "next", callback);
    },
    gotoNextYear: function (callback) {
      this._move("year", "next", callback);
    },
  };
  var logError = function (message) {
    if (window.console) {
      window.console.error(message);
    }
  };
  $.fn.calendario = function (options) {
    var instance = $.data(this, "calendario");
    if (typeof options === "string") {
      var args = Array.prototype.slice.call(arguments, 1);
      this.each(function () {
        if (!instance) {
          logError(
            "cannot call methods on calendario prior to initialization; " +
              "attempted to call method '" +
              options +
              "'",
          );
          return;
        }
        if (!$.isFunction(instance[options]) || options.charAt(0) === "_") {
          logError("no such method '" + options + "' for calendario instance");
          return;
        }
        instance[options].apply(instance, args);
      });
    } else {
      this.each(function () {
        if (instance) {
          instance._init();
        } else {
          instance = $.data(
            this,
            "calendario",
            new $.Calendario(options, this),
          );
        }
      });
    }
    return instance;
  };
})(jQuery, window);

$(function () {
  var transEndEventNames = {
      WebkitTransition: "webkitTransitionEnd",
      MozTransition: "transitionend",
      OTransition: "oTransitionEnd",
      msTransition: "MSTransitionEnd",
      transition: "transitionend",
    },
    transEndEventName = transEndEventNames[Modernizr.prefixed("transition")],
    $wrapper = $("#custom-inner"),
    $calendar = $("#calendar"),
    cal = $calendar.calendario({
      onDayClick: function ($el, $contentEl, dateProperties) {
        if ($contentEl.length > 0) {
          showEvents($contentEl, dateProperties);
        }
      },

      displayWeekAbbr: true,
    }),
    $month = $("#custom-month").html(cal.getMonthName()),
    $year = $("#custom-year").html(cal.getYear());
  $("#custom-next").on("click", function () {
    cal.gotoNextMonth(updateMonthYear);
  });
  $("#custom-prev").on("click", function () {
    cal.gotoPreviousMonth(updateMonthYear);
  });
  function updateMonthYear() {
    $month.html(cal.getMonthName());
    $year.html(cal.getYear());
  }
  // just an example..
  function showEvents($contentEl, dateProperties) {
    hideEvents();
    var $events = $(
        '<div id="custom-content-reveal" class="custom-content-reveal"><h4>Events for ' +
          dateProperties.monthname +
          " " +
          dateProperties.day +
          ", " +
          dateProperties.year +
          "</h4></div>",
      ),
      $close = $('<span class="custom-content-close"></span>').on(
        "click",
        hideEvents,
      );
    $events.append($contentEl.html(), $close).insertAfter($wrapper);
    setTimeout(function () {
      $events.css("top", "0%");
    }, 25);
  }
  function hideEvents() {
    var $events = $("#custom-content-reveal");
    if ($events.length > 0) {
      $events.css("top", "100%");
      Modernizr.csstransitions
        ? $events.on(transEndEventName, function () {
            $(this).remove();
          })
        : $events.remove();
    }
  }
});

$(document).ready(function () {
  if ($("#calendar").length === 0) return;

  var $calendar = $("#calendar");
  var startDate = null;
  var endDate = null;
  var cal;
  var bookedDates = new Set();
  var isSelecting = false;

  function initCalendar() {
    cal = $calendar.calendario({
      onDayClick: function ($el, $contentEl, dateProperties) {
        if ($contentEl.length > 0) {
          showEvents($contentEl, dateProperties);
        }
      },
      displayWeekAbbr: true,
    });

    $("#custom-month").text(cal.getMonthName());
    $("#custom-year").text(cal.getYear());

    var roomId = $('input[name="room_id"]').val();
    if (roomId) loadBookedDates(roomId);
  }

  $("#custom-next").on("click", function () {
    cal.gotoNextMonth(() => {
      updateMonthYear();
      updateSelectedRange();
    });
  });

  $("#custom-prev").on("click", function () {
    cal.gotoPreviousMonth(() => {
      updateMonthYear();
      updateSelectedRange();
    });
  });

  function updateMonthYear() {
    $("#custom-month").text(cal.getMonthName());
    $("#custom-year").text(cal.getYear());

    var roomId = $('input[name="room_id"]').val();
    if (roomId) loadBookedDates(roomId);
  }

  function loadBookedDates(roomId) {
    $.ajax({
      url: "/api/getBookedDates.php",
      type: "GET",
      data: { room_id: roomId },
      dataType: "json",
      success: function (response) {
        if (response.success) {
          bookedDates = new Set(response.bookedDates);
          markBookedDates();
        } else {
          console.error("Помилка отримання дат:", response.message);
        }
      },
      error: function (xhr, status, error) {
        console.error("AJAX error:", status, error);
      },
    });
  }

  function markBookedDates() {
    $(".fc-date").each(function () {
      var dateText = $(this).text().trim();
      var month = cal.getMonth();
      var year = cal.getYear();
      var fullDate = `${year}-${month < 10 ? "0" + month : month}-${dateText < 10 ? "0" + dateText : dateText}`;

      // Додаємо всередину стандартний div-контейнер, якщо його ще немає
      if (!$(this).find(".fc-date-container").length) {
        $(this).wrapInner('<div class="fc-date-container"></div>');
      }

      // Отримуємо внутрішній контейнер для вставки заброньованого блоку
      var $container = $(this).find(".fc-date-container");

      if (bookedDates.has(fullDate)) {
        // Додаємо клас та атрибут
        $container
          .addClass("booked-date")
          .attr("title", "Ця дата вже заброньована");
      } else {
        // Видаляємо, якщо дата вже не заброньована
        $container.removeClass("booked-date").removeAttr("title");
      }
    });
  }

  function clearPreviousSelection() {
    $(".fc-date")
      .parent()
      .removeClass("selected-range selected-start selected-end");
  }

  function disablePastDates() {
    $(".fc-date").each(function () {
      var dateText = $(this).text().trim();
      var month = cal.getMonth();
      var year = cal.getYear();
      var fullDate = `${year}-${month < 10 ? "0" + month : month}-${dateText < 10 ? "0" + dateText : dateText}`;

      var today = new Date();
      var todayYear = today.getFullYear();
      var todayMonth = (today.getMonth() + 1).toString().padStart(2, "0");
      var todayDay = today.getDate().toString().padStart(2, "0");
      var todayDate = `${todayYear}-${todayMonth}-${todayDay}`;

      if (fullDate < todayDate) {
        $(this).addClass("past-date");
      }
    });
  }

  function isRangeValid(start, end) {
    let current = new Date(start);
    let endDate = new Date(end);

    while (current <= endDate) {
      let dateString = current.toISOString().split("T")[0];
      if (bookedDates.has(dateString)) return false;
      current.setDate(current.getDate() + 1);
    }
    return true;
  }

  function updateSelectedRange() {
    clearPreviousSelection();

    if (startDate && endDate) {
      if (!isRangeValid(startDate, endDate)) {
        alert("Обраний діапазон містить заброньовані дати! Оберіть інші дати.");
        startDate = null;
        endDate = null;
        updateSelectedRange();
        return;
      }

      var start = new Date(startDate);
      var end = new Date(endDate);

      while (start <= end) {
        let year = start.getFullYear();
        let month = (start.getMonth() + 1).toString().padStart(2, "0");
        let day = start.getDate().toString().padStart(2, "0");
        let fullDate = `${year}-${month}-${day}`;

        $(".fc-date").each(function () {
          let dateText = $(this).text();
          let currentMonth = cal.getMonth();
          let currentYear = cal.getYear();
          let currentFullDate = `${currentYear}-${currentMonth < 10 ? "0" + currentMonth : currentMonth}-${dateText < 10 ? "0" + dateText : dateText}`;

          if (currentFullDate === startDate) {
            $(this).parent().addClass("selected-start");
          }
          if (currentFullDate === endDate) {
            $(this).parent().addClass("selected-end");
          }
          if (currentFullDate > startDate && currentFullDate < endDate) {
            $(this).parent().addClass("selected-range");
          }
        });

        start.setDate(start.getDate() + 1);
      }
    }

    $("#display-check-in").text(startDate || "Не вибрано");
    $("#display-check-out").text(endDate || "Не вибрано");
    $("#check-in").val(startDate || "");
    $("#check-out").val(endDate || "");
  }

  function debounceClick(callback, delay = 50) {
    if (isSelecting) return;
    isSelecting = true;
    setTimeout(() => {
      callback();
      isSelecting = false;
    }, delay);
  }

  $calendar.on("click", ".fc-date", function () {
    debounceClick(() => {
      var $this = $(this);
      var $cell = $this.parent();

      if ($cell.hasClass("disabled")) {
        alert("Ця дата вже заброньована!");
        return;
      }

      var selectedDate = parseInt($this.text());
      var selectedMonth = cal.getMonth();
      var selectedYear = cal.getYear();
      var fullDate = `${selectedYear}-${selectedMonth < 10 ? "0" + selectedMonth : selectedMonth}-${selectedDate < 10 ? "0" + selectedDate : selectedDate}`;

      // Отримуємо поточну дату
      var today = new Date();
      var todayYear = today.getFullYear();
      var todayMonth = (today.getMonth() + 1).toString().padStart(2, "0");
      var todayDay = today.getDate().toString().padStart(2, "0");
      var todayDate = `${todayYear}-${todayMonth}-${todayDay}`;

      // **Перевіряємо, чи обрана дата не раніше за сьогоднішню**
      if (fullDate < todayDate) {
        alert("Неможливо обрати минулі дати!");
        return;
      }

      // Логіка вибору діапазону бронювання
      if (!startDate || (startDate && endDate)) {
        startDate = fullDate;
        endDate = null;
      } else if (fullDate >= startDate) {
        endDate = fullDate;
      } else {
        startDate = fullDate;
        endDate = null;
      }

      updateSelectedRange();
    });
  });

  $("#reset-dates").on("click", function () {
    startDate = null;
    endDate = null;
    updateSelectedRange();
  });

  $("form").on("submit", function (event) {
    var checkIn = $("#check-in").val();
    var checkOut = $("#check-out").val();

    if (!checkIn || !checkOut) {
      alert("Будь ласка, виберіть дату заїзду та виїзду перед бронюванням!");
      event.preventDefault();
    }
  });

  initCalendar();
  disablePastDates();
});
