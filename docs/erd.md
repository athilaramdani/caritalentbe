---
config:
  layout: elk
---
classDiagram
direction TB
    class User {
	    -id: bigint
	    -name: string
	    -email: string
	    -password: string
	    -phone: string
	    +login() : boolean
	    +logout() : void
	    +updateProfile() : void
    }

    class Admin {
	    +verifyTalent() : void
	    +manageUsers() : void
	    +moderateEvents() : void
    }

    class EventOrganizer {
	    +createEvent() : Event
	    +updateEvent() : void
	    +deleteEvent() : void
	    +sendInvitation() : Invitation
    }

    class Talent {
	    -stageName: string
	    -genre: string
	    -priceMin: int
	    -priceMax: int
	    -city: string
	    -bio: text
	    -portfolioLink: string
	    -verified: boolean
	    +applyEvent() : Application
	    +acceptInvitation() : Booking
    }

    class Event {
	    -id: bigint
	    -title: string
	    -description: text
	    -genreNeeded: string
	    -budget: int
	    -eventDate: date
	    -venueName: string
	    -latitude: float
	    -longitude: float
	    +create() : void
	    +update() : void
	    +cancel() : void
    }

    class Application {
	    -id: bigint
	    -message: string
	    -proposedPrice: int
	    -status: string
	    +submit() : void
	    +cancel() : void
    }

    class Invitation {
	    -id: bigint
	    -offeredPrice: int
	    -status: string
	    +send() : void
	    +accept() : Booking
	    +reject() : void
    }

    class Booking {
	    -id: bigint
	    -agreedPrice: int
	    -status: string
	    +confirm() : void
	    +cancel() : void
    }

    class Review {
	    -id: bigint
	    -rating: int
	    -comment: string
	    +create() : void
    }

    class Notification {
	    -id: bigint
	    -title: string
	    -body: string
	    -type: string
	    -isRead: boolean
	    +send() : void
	    +markAsRead() : void
    }

    User <|-- Admin
    User <|-- EventOrganizer
    User <|-- Talent

    EventOrganizer "1" -- "0..*" Event : creates

    Talent "1" -- "0..*" Application : submits
    Event "1" -- "0..*" Application : receives

    EventOrganizer "1" -- "0..*" Invitation : sends
    Talent "1" -- "0..*" Invitation : receives
    Event "1" -- "0..*" Invitation : for

    Event "1" -- "0..*" Booking : has
    Talent "1" -- "0..*" Booking : performs

    Application "1" -- "0..1" Booking : generates
    Invitation "1" -- "0..1" Booking : generates

    Talent "1" -- "0..*" Review : receives
    Event "1" -- "0..*" Review : has

    User "1" -- "0..*" Notification : receives