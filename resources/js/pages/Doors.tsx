import AppLayout from "@/layouts/app-layout";
import {
    Card,
    Container,
    Group,
    Stack,
    Text,
    Title,
    Badge,
    ThemeIcon,
    SimpleGrid,
    Alert,
    ActionIcon,
    Tooltip,
    Pagination,
    Table,
} from "@mantine/core";
import {
    Building2,
    Key,
    CheckCircle,
    AlertTriangle,
    Clock,
    RefreshCw,
    Calendar,
    Users,
    Zap,
} from "lucide-react";
import React from "react";
import { router, usePage } from "@inertiajs/react";
import DataTable from "@/components/data-table";

type InertiaSalle = {
    id: number;
    hp_id: number;
    libelle_hp: string;
    dorma: number[];
    libelles_matrix: string[] | null;
    cours_count?: number;
    enseignants?: string[];
    has_hp_match?: boolean;
};

const Doors = () => {
    type Paginated<T> = {
        data: T[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };

    const { props } = usePage<{
        salles: Paginated<InertiaSalle>;
        filters?: { q?: string };
    }>();
    const salles = props.salles;
    const sallesData = salles?.data || [];
    const currentQuery = props.filters?.q || "";

    const stats = {
        totalSalles: salles?.total ?? sallesData.length,
        sallesConnectees: salles?.total ?? sallesData.length, // par défaut: considérées connectées si en base
        totalSerrures: sallesData.reduce(
            (acc, s) => acc + (Array.isArray(s.dorma) ? s.dorma.length : 0),
            0
        ),
        coursAujourdhui: sallesData.reduce(
            (acc, s) => acc + (s.cours_count || 0),
            0
        ),
    };

    // placeholders for columns we don't yet have (last sync/next course/status)

    return (
        <AppLayout active="salles">
            <Container size="xl" className="py-6">
                <Stack gap="lg">
                    {/* Header */}
                    <Group justify="space-between" align="flex-end">
                        <div>
                            <Title
                                order={1}
                                className="text-3xl font-bold text-gray-800"
                            >
                                Gestion des Salles
                            </Title>
                            <Text c="dimmed" size="lg">
                                État de synchronisation et serrures connectées
                            </Text>
                        </div>
                        <Group>
                            <Tooltip label="Forcer la synchronisation">
                                <ActionIcon
                                    size="lg"
                                    variant="light"
                                    color="blue"
                                >
                                    <RefreshCw size={18} />
                                </ActionIcon>
                            </Tooltip>
                        </Group>
                    </Group>

                    {/* Stats Overview */}
                    <SimpleGrid cols={{ base: 1, sm: 2, md: 4 }} spacing="lg">
                        <Card shadow="sm" padding="lg" radius="md" withBorder>
                            <Group justify="space-between" align="flex-start">
                                <div style={{ flex: 1 }}>
                                    <Text size="sm" c="dimmed" mb="xs">
                                        Salles totales
                                    </Text>
                                    <Text size="2xl" fw={700} c="blue">
                                        {stats.totalSalles}
                                    </Text>
                                    <Text size="xs" c="dimmed" mt="xs">
                                        Avec serrures Dorma
                                    </Text>
                                </div>
                                <ThemeIcon
                                    size={40}
                                    variant="light"
                                    color="blue"
                                >
                                    <Building2 size={20} />
                                </ThemeIcon>
                            </Group>
                        </Card>

                        <Card shadow="sm" padding="lg" radius="md" withBorder>
                            <Group justify="space-between" align="flex-start">
                                <div style={{ flex: 1 }}>
                                    <Text size="sm" c="dimmed" mb="xs">
                                        Salles connectées
                                    </Text>
                                    <Text size="2xl" fw={700} c="green">
                                        {stats.sallesConnectees}
                                    </Text>
                                    <Text size="xs" c="dimmed" mt="xs">
                                        {Math.round(
                                            (stats.sallesConnectees /
                                                stats.totalSalles) *
                                                100
                                        )}
                                        % opérationnelles
                                    </Text>
                                </div>
                                <ThemeIcon
                                    size={40}
                                    variant="light"
                                    color="green"
                                >
                                    <CheckCircle size={20} />
                                </ThemeIcon>
                            </Group>
                        </Card>

                        <Card shadow="sm" padding="lg" radius="md" withBorder>
                            <Group justify="space-between" align="flex-start">
                                <div style={{ flex: 1 }}>
                                    <Text size="sm" c="dimmed" mb="xs">
                                        Serrures actives
                                    </Text>
                                    <Text size="2xl" fw={700} c="violet">
                                        {stats.totalSerrures}
                                    </Text>
                                    <Text size="xs" c="dimmed" mt="xs">
                                        Système Dorma
                                    </Text>
                                </div>
                                <ThemeIcon
                                    size={40}
                                    variant="light"
                                    color="violet"
                                >
                                    <Key size={20} />
                                </ThemeIcon>
                            </Group>
                        </Card>

                        <Card shadow="sm" padding="lg" radius="md" withBorder>
                            <Group justify="space-between" align="flex-start">
                                <div style={{ flex: 1 }}>
                                    <Text size="sm" c="dimmed" mb="xs">
                                        Cours aujourd'hui
                                    </Text>
                                    <Text size="2xl" fw={700} c="orange">
                                        {stats.coursAujourdhui}
                                    </Text>
                                    <Text size="xs" c="dimmed" mt="xs">
                                        Synchronisés
                                    </Text>
                                </div>
                                <ThemeIcon
                                    size={40}
                                    variant="light"
                                    color="orange"
                                >
                                    <Calendar size={20} />
                                </ThemeIcon>
                            </Group>
                        </Card>
                    </SimpleGrid>

                    {/* Status Alert */}
                    <Alert
                        icon={<Zap size={16} />}
                        title="Synchronisation des salles"
                        color="blue"
                        variant="light"
                    >
                        <Text size="sm">
                            Données issues de la base locale (Hyperplanning →
                            salles avec serrures Dorma)
                        </Text>
                    </Alert>

                    {/* Salles Table */}
                    <DataTable
                        title="État des Salles"
                        searchPlaceholder="Rechercher une salle..."
                        initialSearch={currentQuery}
                        onSearch={(q) =>
                            router.get(
                                "/salles",
                                { q },
                                { preserveScroll: true, preserveState: true }
                            )
                        }
                        pagination={{
                            current_page: salles.current_page,
                            last_page: salles.last_page,
                        }}
                        onPageChange={(page) =>
                            router.get(
                                "/salles",
                                { page, q: currentQuery },
                                { preserveScroll: true, preserveState: true }
                            )
                        }
                        rightSection={
                            <Badge color="blue" variant="light">
                                {stats.sallesConnectees}/{stats.totalSalles}{" "}
                                connectées
                            </Badge>
                        }
                        headers={[
                            <Table.Th>Salle</Table.Th>,
                            <Table.Th>Correspondance HP</Table.Th>,
                            <Table.Th>Libellés Matrix</Table.Th>,
                            <Table.Th>Serrures Dorma</Table.Th>,
                            <Table.Th>Cours</Table.Th>,
                            <Table.Th>Enseignants</Table.Th>,
                        ]}
                    >
                        {sallesData.map((salle) => (
                            <Table.Tr key={salle.id}>
                                <Table.Td>
                                    <div>
                                        <Text size="sm" fw={500}>
                                            {salle.libelle_hp}
                                        </Text>
                                        <Text size="xs" c="dimmed">
                                            HP ID: {salle.hp_id}
                                        </Text>
                                    </div>
                                </Table.Td>
                                <Table.Td>
                                    <Badge
                                        color={
                                            salle.has_hp_match
                                                ? "green"
                                                : "orange"
                                        }
                                        variant="light"
                                        size="sm"
                                    >
                                        {salle.has_hp_match
                                            ? salle.libelle_hp || "Trouvée"
                                            : "Manquante"}
                                    </Badge>
                                </Table.Td>
                                <Table.Td>
                                    <Group gap="xs">
                                        {salle.libelles_matrix?.length ? (
                                            salle.libelles_matrix.map(
                                                (label, index) => (
                                                    <Badge
                                                        key={index}
                                                        size="xs"
                                                        variant="outline"
                                                        color="blue"
                                                    >
                                                        {label}
                                                    </Badge>
                                                )
                                            )
                                        ) : (
                                            <Badge
                                                size="xs"
                                                variant="light"
                                                color="gray"
                                            >
                                                Non synchronisé
                                            </Badge>
                                        )}
                                    </Group>
                                </Table.Td>
                                <Table.Td>
                                    <Group gap="xs">
                                        {(salle.dorma || []).map(
                                            (dormaId, index) => (
                                                <Badge
                                                    key={index}
                                                    size="xs"
                                                    variant="light"
                                                    color="violet"
                                                >
                                                    {dormaId}
                                                </Badge>
                                            )
                                        )}
                                    </Group>
                                </Table.Td>
                                <Table.Td>
                                    <Group gap="xs" align="center">
                                        <Text size="sm" fw={500}>
                                            {salle.cours_count}
                                        </Text>
                                        <Text size="xs" c="dimmed">
                                            cours
                                        </Text>
                                    </Group>
                                </Table.Td>
                                <Table.Td>
                                    <Group gap="xs" align="center">
                                        <ThemeIcon
                                            size="xs"
                                            color="blue"
                                            variant="light"
                                        >
                                            <Users size={10} />
                                        </ThemeIcon>
                                        <Text size="sm" fw={500}>
                                            {salle.enseignants?.length || 0}
                                        </Text>
                                    </Group>
                                </Table.Td>
                            </Table.Tr>
                        ))}
                    </DataTable>
                </Stack>
            </Container>
        </AppLayout>
    );
};

export default Doors;
