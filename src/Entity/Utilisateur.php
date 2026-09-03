<?php

namespace App\Entity;

use App\Repository\UtilisateurRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UtilisateurRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class Utilisateur implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(length: 100)]
    private ?string $prenom = null;

    #[ORM\Column(length: 100)]
    private ?string $telephone = null;

    #[ORM\Column]
    private ?bool $estVerifie = null;

    /**
     * @var Collection<int, Vehicule>
     */
    #[ORM\OneToMany(targetEntity: Vehicule::class, mappedBy: 'proprietaire')]
    private Collection $vehicules;

    /**
     * @var Collection<int, DocumentIdentite>
     */
    #[ORM\OneToMany(targetEntity: DocumentIdentite::class, mappedBy: 'utilisateur', orphanRemoval: true)]
    private Collection $documentsIdentite;

    /**
     * @var Collection<int, Trajet>
     */
    #[ORM\OneToMany(targetEntity: Trajet::class, mappedBy: 'conducteur')]
    private Collection $trajetsPublies;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'passager')]
    private Collection $reservationsPassager;

    /**
     * @var Collection<int, Avis>
     */
    #[ORM\OneToMany(targetEntity: Avis::class, mappedBy: 'auteur')]
    private Collection $avisRediges;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $photo = null;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    public function __construct()
    {
        $this->vehicules = new ArrayCollection();
        $this->documentsIdentite = new ArrayCollection();
        $this->trajetsPublies = new ArrayCollection();
        $this->reservationsPassager = new ArrayCollection();
        $this->avisRediges = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }

    public function isEstVerifie(): ?bool
    {
        return $this->estVerifie;
    }

    public function setEstVerifie(bool $estVerifie): static
    {
        $this->estVerifie = $estVerifie;

        return $this;
    }

    /**
     * @return Collection<int, Vehicule>
     */
    public function getVehicules(): Collection
    {
        return $this->vehicules;
    }

    public function addVehicule(Vehicule $vehicule): static
    {
        if (!$this->vehicules->contains($vehicule)) {
            $this->vehicules->add($vehicule);
            $vehicule->setProprietaire($this);
        }

        return $this;
    }

    public function removeVehicule(Vehicule $vehicule): static
    {
        if ($this->vehicules->removeElement($vehicule)) {
            // set the owning side to null (unless already changed)
            if ($vehicule->getProprietaire() === $this) {
                $vehicule->setProprietaire(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, DocumentIdentite>
     */
    public function getDocumentsIdentite(): Collection
    {
        return $this->documentsIdentite;
    }

    public function addDocumentsIdentite(DocumentIdentite $documentsIdentite): static
    {
        if (!$this->documentsIdentite->contains($documentsIdentite)) {
            $this->documentsIdentite->add($documentsIdentite);
            $documentsIdentite->setUtilisateur($this);
        }

        return $this;
    }

    public function removeDocumentsIdentite(DocumentIdentite $documentsIdentite): static
    {
        if ($this->documentsIdentite->removeElement($documentsIdentite)) {
            // set the owning side to null (unless already changed)
            if ($documentsIdentite->getUtilisateur() === $this) {
                $documentsIdentite->setUtilisateur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Trajet>
     */
    public function getTrajetsPublies(): Collection
    {
        return $this->trajetsPublies;
    }

    public function addTrajetsPubly(Trajet $trajetsPubly): static
    {
        if (!$this->trajetsPublies->contains($trajetsPubly)) {
            $this->trajetsPublies->add($trajetsPubly);
            $trajetsPubly->setConducteur($this);
        }

        return $this;
    }

    public function removeTrajetsPubly(Trajet $trajetsPubly): static
    {
        if ($this->trajetsPublies->removeElement($trajetsPubly)) {
            // set the owning side to null (unless already changed)
            if ($trajetsPubly->getConducteur() === $this) {
                $trajetsPubly->setConducteur(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservationsPassager(): Collection
    {
        return $this->reservationsPassager;
    }

    public function addReservationsPassager(Reservation $reservationsPassager): static
    {
        if (!$this->reservationsPassager->contains($reservationsPassager)) {
            $this->reservationsPassager->add($reservationsPassager);
            $reservationsPassager->setPassager($this);
        }

        return $this;
    }

    public function removeReservationsPassager(Reservation $reservationsPassager): static
    {
        if ($this->reservationsPassager->removeElement($reservationsPassager)) {
            // set the owning side to null (unless already changed)
            if ($reservationsPassager->getPassager() === $this) {
                $reservationsPassager->setPassager(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Avis>
     */
    public function getAvisRediges(): Collection
    {
        return $this->avisRediges;
    }

    public function addAvisRedige(Avis $avisRedige): static
    {
        if (!$this->avisRediges->contains($avisRedige)) {
            $this->avisRediges->add($avisRedige);
            $avisRedige->setAuteur($this);
        }

        return $this;
    }

    public function removeAvisRedige(Avis $avisRedige): static
    {
        if ($this->avisRediges->removeElement($avisRedige)) {
            // set the owning side to null (unless already changed)
            if ($avisRedige->getAuteur() === $this) {
                $avisRedige->setAuteur(null);
            }
        }

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }
}
