import AppLayout from '@/layouts/app-layout'
import { Badge, Container, Group, Stack, Text, ThemeIcon } from '@mantine/core'
import { Table } from '@mantine/core'
import { Users, Calendar, CheckCircle } from 'lucide-react'
import React from 'react'
import { router, usePage } from '@inertiajs/react'
import DataTable from '@/components/data-table'

type EnseignantRow = {
  id: number
  nom: string
  prenom: string
  matricule: string
  cours_count: number
  authorisations_count: number
  last_course_date: string | null
}

const Teachers = () => {
  type Paginated<T> = {
    data: T[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }

  const { props } = usePage<{ enseignants: Paginated<EnseignantRow>, filters?: { q?: string } }>()
  const paged = props.enseignants
  const rows = paged?.data || []
  const currentQuery = props.filters?.q || ''

  return (
    <AppLayout active='enseignants'>
      <Container size="xl" className="py-6">
        <Stack gap="lg">
          <DataTable
            title="Enseignants"
            searchPlaceholder="Rechercher nom, prénom ou matricule..."
            initialSearch={currentQuery}
            onSearch={(q) => router.get('/enseignants', { q }, { preserveScroll: true, preserveState: true })}
            pagination={{ current_page: paged.current_page, last_page: paged.last_page }}
            onPageChange={(page) => router.get('/enseignants', { page, q: currentQuery }, { preserveScroll: true, preserveState: true })}
            rightSection={
              <Badge color="blue" variant="light">
                {paged?.total ?? rows.length} au total
              </Badge>
            }
            headers={[ 'Identité', 'Matricule', 'Cours', 'Autorisations', 'Dernier cours' ]}
          >
            {rows.map((e) => (
              <Table.Tr key={e.id}>
                <Table.Td>
                  <Group gap="xs" align="center">
                    <ThemeIcon size="xs" color="blue" variant="light">
                      <Users size={10} />
                    </ThemeIcon>
                    <Text size="sm" fw={500}>{e.prenom} {e.nom}</Text>
                  </Group>
                </Table.Td>
                <Table.Td>
                  <Text size="sm" c="dimmed">{e.matricule}</Text>
                </Table.Td>
                <Table.Td>
                  <Group gap="xs" align="center">
                    <Text size="sm" fw={500}>{e.cours_count}</Text>
                    <Text size="xs" c="dimmed">cours</Text>
                  </Group>
                </Table.Td>
                <Table.Td>
                  <Group gap="xs" align="center">
                    <ThemeIcon size="xs" color="teal" variant="light">
                      <CheckCircle size={10} />
                    </ThemeIcon>
                    <Text size="sm">{e.authorisations_count}</Text>
                  </Group>
                </Table.Td>
                <Table.Td>
                  <Group gap="xs" align="center">
                    <ThemeIcon size="xs" color="violet" variant="light">
                      <Calendar size={10} />
                    </ThemeIcon>
                    <Text size="sm">{e.last_course_date ? new Date(e.last_course_date).toLocaleDateString('fr-FR') : '—'}</Text>
                  </Group>
                </Table.Td>
              </Table.Tr>
            ))}
          </DataTable>
        </Stack>
      </Container>
    </AppLayout>
  )
}

export default Teachers


