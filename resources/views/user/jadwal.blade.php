@extends('user.layouts.app')

@section('title', 'Jadwal')

@section('content')
    <!-- Section Header -->
    <section id="header-section" class="mb-5">
        <h1>Daftar Jadwal</h1>
    </section>

    <div style="padding: 70px;">
        <div id="calendar"></div>
        {{-- <div id="event-list">
        <h3>Events on <span id="selected-date">Select a date</span></h3>
        <ul id="events">
            <li>No events for this date</li>
        </ul>
    </div> --}}
        <div class="container mt-5">
            <h1 class="text-center mb-4">Events List</h1>
            <div class="row justify-content-center">
                @foreach ($events as $event)
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm" style="border: none; background-color: #f8f9fa;">
                            <div class="card-body">
                                <h5 class="card-title text-primary">{{ $event->title }}</h5>
                                <p class="card-text text-muted">{{ $event->description }}</p>
                            </div>
                            <div class="card-footer bg-transparent border-top-0">
                                <p class="mb-1 text-secondary"><strong>Start:</strong>
                                    {{ \Carbon\Carbon::parse($event->start_time)->translatedFormat('l, d F Y H:i') }} WIB
                                </p>
                                <p class="mb-1 text-secondary"><strong>End:</strong>
                                    {{ \Carbon\Carbon::parse($event->end_time)->translatedFormat('l, d F Y H:i') }} WIB</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var calendarEl = document.getElementById('calendar');
                var events = {!! $eventsJson !!};

                var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    events: events,
                    locale: 'id', // Indonesian locale
                    dateClick: function(info) {
                        var selectedDate = info.dateStr;
                        var eventList = events.filter(function(event) {
                            return event.start.startsWith(selectedDate);
                        });

                        var eventListEl = document.getElementById('events');
                        var selectedDateEl = document.getElementById('selected-date');

                        eventListEl.innerHTML = '';
                        selectedDateEl.textContent = selectedDate;

                        if (eventList.length > 0) {
                            eventList.forEach(function(event) {
                                var li = document.createElement('li');
                                li.textContent = event.title + ' (' + new Date(event.start)
                                    .toLocaleTimeString([], {
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    }) + ' - ' + new Date(event.end).toLocaleTimeString([], {
                                        hour: '2-digit',
                                        minute: '2-digit'
                                    }) + ')';
                                eventListEl.appendChild(li);
                            });
                        } else {
                            eventListEl.innerHTML = '<li>No events for this date</li>';
                        }
                    }
                });

                calendar.render();
            });
        </script>
    </div>

@endsection
