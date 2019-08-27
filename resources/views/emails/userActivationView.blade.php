<div>
    <h2> Beste {{ $userName  }}, </h2>
    <span>
        Er is een account gemaakt met de volgende mail: {{ $email }}.
        Klik op de link om u account te activeren en u wachtwoord te wijzigen.
    </span>
    <a href="{{$link}}">
        <button>
            Account activeren
        </button>
    </a>
</div>