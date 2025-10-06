import React, { ReactNode } from 'react'
import { Card, Group, Title, TextInput, Badge, Table, Pagination } from '@mantine/core'
import { Search } from 'lucide-react'

type PaginationInfo = {
  current_page: number
  last_page: number
}

type Props = {
  title: string
  searchPlaceholder?: string
  initialSearch?: string
  onSearch: (q: string) => void
  pagination?: PaginationInfo
  onPageChange?: (page: number) => void
  rightSection?: ReactNode
  headers: ReactNode[]
  children: ReactNode
}

const DataTable = ({
  title,
  searchPlaceholder = 'Rechercher...',
  initialSearch = '',
  onSearch,
  pagination,
  onPageChange,
  rightSection,
  headers,
  children,
}: Props) => {
  return (
    <Card shadow="sm" padding="lg" radius="md" withBorder>
      <Group justify="space-between" align="center" mb="md">
        <Title order={3}>{title}</Title>
        <Group gap="md">
          <TextInput
            placeholder={searchPlaceholder}
            leftSection={<Search size={16} />}
            defaultValue={initialSearch}
            onKeyDown={(e) => {
              if (e.key === 'Enter') {
                const value = (e.target as HTMLInputElement).value
                onSearch(value)
              }
            }}
          />
          {rightSection}
        </Group>
      </Group>

      <Table striped highlightOnHover>
        <Table.Thead>
          <Table.Tr>
            {headers.map((h, idx) => (
              <Table.Th key={idx}>{h}</Table.Th>
            ))}
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody>{children}</Table.Tbody>
      </Table>

      {pagination && pagination.last_page > 1 && onPageChange && (
        <Group justify="center" mt="md">
          <Pagination
            total={pagination.last_page}
            value={pagination.current_page}
            onChange={(page) => onPageChange(page)}
          />
        </Group>
      )}
    </Card>
  )
}

export default DataTable


